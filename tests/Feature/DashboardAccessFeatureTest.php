<?php

use App\Models\JenisPembayaran;
use App\Models\Pembayaran;
use App\Models\Warga;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Models\User;

it('allows admin to access admin dashboard', function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin)
        ->get('/admin/dashboard')
        ->assertOk();
});

it('forbids user role from admin dashboard', function () {
    $user = User::factory()->create();
    $user->role = 'user';
    $user->save();

    $this->actingAs($user)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

it('allows user role to access user dashboard and tagihan', function () {
    $user = User::factory()->create();
    $user->role = 'user';
    $user->save();

    $this->actingAs($user)
        ->get('/user/dashboard')
        ->assertOk();

    $this->actingAs($user)
        ->get(route('user.tagihan'))
        ->assertOk();
});

it('forbids admin role from user dashboard', function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin)
        ->get('/user/dashboard')
        ->assertForbidden();
});

it('allows admin to access overdue recap page', function () {
    $admin = User::factory()->create();
    $admin->role = 'admin';
    $admin->save();

    $this->actingAs($admin)
        ->get(route('rekap.tunggakan'))
        ->assertOk();
});

it('calculates water billing automatically when admin creates air payment', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $warga = Warga::create([
        'nama' => 'Warga Air',
        'nik' => '1234567890123456',
        'alamat' => 'Jl. Air Bersih',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Air',
        'keterangan' => 'Tagihan HIPPAM bulanan',
        'nominal' => 0,
    ]);

    $response = $this->actingAs($admin)->post(route('pembayaran.store'), [
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => '2026-04-10',
        'periode' => '2026-04',
        'meter_awal' => 120,
        'meter_akhir' => 140,
        'denda' => 2500,
        'jatuh_tempo' => '2026-04-20',
        'keterangan' => 'Petugas mencatat meter rumah',
    ]);

    $response->assertRedirect(route('pembayaran.index'));

    $pembayaran = Pembayaran::first();

    expect($pembayaran)->not->toBeNull();
    expect($pembayaran->jumlah)->toBe(37500);
    expect($pembayaran->pemakaian_air)->toBe(20);
    expect($pembayaran->tarif_per_meter)->toBe(1500);
    expect($pembayaran->biaya_tetap)->toBe(5000);
    expect($pembayaran->denda)->toBe(2500);
    expect($pembayaran->periode)->toBe('2026-04');
});

it('rejects water billing when meter akhir is lower than meter awal', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $warga = Warga::create([
        'nama' => 'Warga Air 2',
        'nik' => '2234567890123456',
        'alamat' => 'Jl. Air Jernih',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Air',
        'keterangan' => 'Tagihan air',
        'nominal' => 0,
    ]);

    $this->actingAs($admin)
        ->post(route('pembayaran.store'), [
            'warga_id' => $warga->id,
            'jenis_pembayaran_id' => $jenis->id,
            'tanggal_bayar' => '2026-04-10',
            'periode' => '2026-04',
            'meter_awal' => 140,
            'meter_akhir' => 120,
            'denda' => 0,
        ])
        ->assertSessionHasErrors(['meter_akhir']);
});

it('applies late fee automatically to overdue water bill', function () {
    $warga = Warga::create([
        'nama' => 'Warga Denda',
        'nik' => '3234567890123456',
        'alamat' => 'Jl. Tunggakan',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Air',
        'keterangan' => 'Tagihan air',
        'nominal' => 0,
    ]);

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => '2026-03-10',
        'periode' => '2026-03',
        'meter_awal' => 100,
        'meter_akhir' => 110,
        'pemakaian_air' => 10,
        'tarif_per_meter' => 1500,
        'biaya_tetap' => 5000,
        'denda' => 0,
        'jatuh_tempo' => now()->subDays(5)->toDateString(),
        'jumlah' => 20000,
        'keterangan' => 'Tagihan air',
        'status' => 'pending',
        'invoice' => 'INV-DENDA-001',
    ]);

    Artisan::call('tagihan:apply-denda');

    $pembayaran->refresh();

    expect($pembayaran->denda)->toBe(2500);
    expect($pembayaran->jumlah)->toBe(22500);
    expect($pembayaran->keterangan)->toContain('Denda keterlambatan otomatis');
});

it('generates draft water bills once per period', function () {
    $warga1 = Warga::create([
        'nama' => 'Warga Satu',
        'nik' => '1111111111111111',
        'alamat' => 'Jl. Melati 1',
    ]);

    $warga2 = Warga::create([
        'nama' => 'Warga Dua',
        'nik' => '2222222222222222',
        'alamat' => 'Jl. Melati 2',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Air',
        'keterangan' => 'Tagihan air',
        'nominal' => 0,
    ]);

    Pembayaran::create([
        'warga_id' => $warga1->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => '2026-03-10',
        'periode' => '2026-03',
        'meter_awal' => 100,
        'meter_akhir' => 120,
        'pemakaian_air' => 20,
        'tarif_per_meter' => 1500,
        'biaya_tetap' => 5000,
        'denda' => 0,
        'jatuh_tempo' => '2026-03-20',
        'jumlah' => 35000,
        'keterangan' => 'Lunas',
        'status' => 'paid',
        'invoice' => 'INV-OLD-001',
    ]);

    Artisan::call('tagihan:generate-air', ['--periode' => '2026-04']);

    expect(Pembayaran::where('periode', '2026-04')->count())->toBe(2);

    $draft = Pembayaran::where('periode', '2026-04')->where('warga_id', $warga2->id)->first();

    expect($draft)->not->toBeNull();
    expect($draft->status)->toBe('pending');
    expect($draft->jumlah)->toBe(0);
    expect($draft->meter_awal)->toBe(0);
    expect($draft->meter_akhir)->toBe(0);

    Artisan::call('tagihan:generate-air', ['--periode' => '2026-04']);

    expect(Pembayaran::where('periode', '2026-04')->count())->toBe(2);
});

it('blocks midtrans payment for draft bills', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $warga = Warga::create([
        'nama' => 'Warga Draft',
        'nik' => '3333333333333333',
        'alamat' => 'Jl. Mawar',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Air',
        'keterangan' => 'Tagihan air',
        'nominal' => 0,
    ]);

    $draft = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'periode' => now()->format('Y-m'),
        'meter_awal' => 0,
        'meter_akhir' => 0,
        'pemakaian_air' => 0,
        'tarif_per_meter' => 1500,
        'biaya_tetap' => 5000,
        'denda' => 0,
        'jatuh_tempo' => now()->addDays(10)->toDateString(),
        'jumlah' => 0,
        'keterangan' => 'Draft',
        'status' => 'pending',
        'invoice' => 'INV-DRAFT-001',
    ]);

    $this->actingAs($admin)
        ->get(route('pembayaran.pay', $draft->id))
        ->assertRedirect(route('pembayaran.index'));
});

it('sends whatsapp reminder only once for H-5 bills', function () {
    config([
        'services.whatsapp.enabled' => true,
        'services.whatsapp.token' => 'test-token',
        'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        'services.whatsapp.country_code' => '62',
    ]);

    Http::fake([
        'api.fonnte.com/send' => Http::response(['status' => true], 200),
    ]);

    $warga = Warga::create([
        'nama' => 'Warga WA',
        'nik' => '4444444444444444',
        'alamat' => 'Jl. WA',
        'no_hp' => '081234567890',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Air',
        'keterangan' => 'Tagihan air',
        'nominal' => 0,
    ]);

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'periode' => now()->format('Y-m'),
        'meter_awal' => 100,
        'meter_akhir' => 120,
        'pemakaian_air' => 20,
        'tarif_per_meter' => 1500,
        'biaya_tetap' => 5000,
        'denda' => 0,
        'jatuh_tempo' => now()->addDays(5)->toDateString(),
        'jumlah' => 35000,
        'keterangan' => 'Tagihan air',
        'status' => 'pending',
        'invoice' => 'INV-WA-001',
    ]);

    Artisan::call('tagihan:send-whatsapp-reminder');

    Http::assertSentCount(1);
    $pembayaran->refresh();
    expect(Carbon::parse($pembayaran->last_whatsapp_reminder_at)->toDateString())->toBe(now()->toDateString());

    Artisan::call('tagihan:send-whatsapp-reminder');
    Http::assertSentCount(1);
});

it('allows admin to send manual whatsapp reminder from overdue recap', function () {
    config([
        'services.whatsapp.enabled' => true,
        'services.whatsapp.token' => 'test-token',
        'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        'services.whatsapp.country_code' => '62',
    ]);

    Http::fake([
        'api.fonnte.com/send' => Http::response(['status' => true], 200),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $warga = Warga::create([
        'nama' => 'Warga Manual WA',
        'nik' => '5555555555555555',
        'alamat' => 'Jl. Manual',
        'no_hp' => '081234000999',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Air',
        'keterangan' => 'Tagihan air',
        'nominal' => 0,
    ]);

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'periode' => now()->format('Y-m'),
        'meter_awal' => 50,
        'meter_akhir' => 70,
        'pemakaian_air' => 20,
        'tarif_per_meter' => 1500,
        'biaya_tetap' => 5000,
        'denda' => 0,
        'jatuh_tempo' => now()->subDay()->toDateString(),
        'jumlah' => 35000,
        'keterangan' => 'Tagihan manual WA',
        'status' => 'pending',
        'invoice' => 'INV-MANUAL-WA-001',
    ]);

    $this->actingAs($admin)
        ->post(route('pembayaran.reminder-whatsapp', $pembayaran->id))
        ->assertRedirect();

    Http::assertSentCount(1);

    $pembayaran->refresh();
    expect(Carbon::parse($pembayaran->last_whatsapp_reminder_at)->toDateString())->toBe(now()->toDateString());
});

it('allows admin to send bulk whatsapp reminder per warga', function () {
    config([
        'services.whatsapp.enabled' => true,
        'services.whatsapp.token' => 'test-token',
        'services.whatsapp.endpoint' => 'https://api.fonnte.com/send',
        'services.whatsapp.country_code' => '62',
    ]);

    Http::fake([
        'api.fonnte.com/send' => Http::response(['status' => true], 200),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $warga = Warga::create([
        'nama' => 'Warga Bulk WA',
        'nik' => '6666666666666666',
        'alamat' => 'Jl. Bulk',
        'no_hp' => '081299991111',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Air',
        'keterangan' => 'Tagihan air',
        'nominal' => 0,
    ]);

    Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'periode' => now()->format('Y-m'),
        'meter_awal' => 10,
        'meter_akhir' => 20,
        'pemakaian_air' => 10,
        'tarif_per_meter' => 1500,
        'biaya_tetap' => 5000,
        'denda' => 2500,
        'jatuh_tempo' => now()->subDays(2)->toDateString(),
        'jumlah' => 22500,
        'keterangan' => 'Tagihan 1',
        'status' => 'pending',
        'invoice' => 'INV-BULK-001',
    ]);

    Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'periode' => now()->addMonth()->format('Y-m'),
        'meter_awal' => 20,
        'meter_akhir' => 30,
        'pemakaian_air' => 10,
        'tarif_per_meter' => 1500,
        'biaya_tetap' => 5000,
        'denda' => 2500,
        'jatuh_tempo' => now()->subDay()->toDateString(),
        'jumlah' => 22500,
        'keterangan' => 'Tagihan 2',
        'status' => 'pending',
        'invoice' => 'INV-BULK-002',
    ]);

    $this->actingAs($admin)
        ->post(route('pembayaran.reminder-whatsapp.warga', $warga->id))
        ->assertRedirect();

    Http::assertSentCount(1);

    $updatedCount = Pembayaran::query()
        ->where('warga_id', $warga->id)
        ->whereNotNull('last_whatsapp_reminder_at')
        ->count();

    expect($updatedCount)->toBe(2);

    $this->actingAs($admin)
        ->post(route('pembayaran.reminder-whatsapp.warga', $warga->id))
        ->assertRedirect();

    Http::assertSentCount(1);
});

it('allows admin to process cash payment with change', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $warga = Warga::create([
        'nama' => 'Warga Tunai',
        'nik' => '7777777777777777',
        'alamat' => 'Jl. Tunai',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Donasi',
        'keterangan' => 'Donasi sukarela',
        'nominal' => 0,
    ]);

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 50000,
        'keterangan' => 'Tagihan tunai',
        'status' => 'pending',
        'invoice' => 'INV-CASH-001',
    ]);

    $this->actingAs($admin)
        ->post(route('pembayaran.cash.pay', $pembayaran->id), [
            'cash_received_amount' => 100000,
            'catatan_tunai' => 'Bayar di kantor desa',
        ])
        ->assertRedirect(route('pembayaran.invoice', $pembayaran->id));

    $fresh = $pembayaran->fresh();

    expect($fresh->status)->toBe('paid');
    expect($fresh->payment_method)->toBe('cash');
    expect($fresh->cash_received_amount)->toBe(100000);
    expect($fresh->cash_change_amount)->toBe(50000);
    expect($fresh->paid_by_user_id)->toBe($admin->id);
    expect($fresh->keterangan)->toContain('Pembayaran tunai');
});
