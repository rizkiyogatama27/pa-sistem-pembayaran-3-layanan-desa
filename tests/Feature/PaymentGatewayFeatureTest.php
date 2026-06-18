<?php

use App\Models\JenisPembayaran;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Warga;

function makeMidtransSignature(string $orderId, string $statusCode, string $grossAmount, string $serverKey): string
{
    return hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
}

it('rejects midtrans callback with invalid signature', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Ani',
        'nik' => '1111111111111111',
        'alamat' => 'Jl. Kenanga',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Sampah',
        'keterangan' => 'Bulanan',
        'nominal' => 10000,
    ]);

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 10000,
        'keterangan' => 'Tagihan',
        'status' => 'pending',
        'invoice' => 'INV-TEST-001',
    ]);

    $this->postJson(route('midtrans.callback'), [
        'order_id' => 'INV-TEST-001',
        'status_code' => '200',
        'gross_amount' => '10000',
        'transaction_status' => 'settlement',
        'signature_key' => 'invalid-signature',
    ])->assertStatus(403);

    expect($pembayaran->fresh()->status)->toBe('pending');
});

it('marks pembayaran as paid on valid settlement callback', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Beni',
        'nik' => '2222222222222222',
        'alamat' => 'Jl. Melati',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Air',
        'keterangan' => 'Bulanan',
        'nominal' => 20000,
    ]);

    $invoice = 'INV-TEST-002';
    $grossAmount = '20000';
    $statusCode = '200';

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 20000,
        'keterangan' => 'Tagihan',
        'status' => 'pending',
        'invoice' => $invoice,
    ]);

    $signature = makeMidtransSignature($invoice, $statusCode, $grossAmount, 'test-server-key');

    $this->postJson(route('midtrans.callback'), [
        'order_id' => $invoice,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'transaction_status' => 'settlement',
        'signature_key' => $signature,
    ])->assertOk();

    $fresh = $pembayaran->fresh();

    expect($fresh->status)->toBe('paid');
    expect($fresh->tanggal_bayar)->not->toBeNull();
});

it('does not downgrade paid pembayaran back to pending on later callback', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Cici',
        'nik' => '3333333333333333',
        'alamat' => 'Jl. Anggrek',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Donasi',
        'keterangan' => 'Bulanan',
        'nominal' => 30000,
    ]);

    $invoice = 'INV-TEST-003';

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 30000,
        'keterangan' => 'Tagihan',
        'status' => 'paid',
        'invoice' => $invoice,
    ]);

    $signature = makeMidtransSignature($invoice, '200', '30000', 'test-server-key');

    $this->postJson(route('midtrans.callback'), [
        'order_id' => $invoice,
        'status_code' => '200',
        'gross_amount' => '30000',
        'transaction_status' => 'pending',
        'signature_key' => $signature,
    ])->assertOk();

    expect($pembayaran->fresh()->status)->toBe('paid');
});

it('forbids user from paying another warga tagihan', function () {
    $wargaOwner = Warga::create([
        'nama' => 'Dodi',
        'nik' => '4444444444444444',
        'alamat' => 'Jl. Kamboja',
    ]);

    $wargaOther = Warga::create([
        'nama' => 'Eka',
        'nik' => '5555555555555555',
        'alamat' => 'Jl. Flamboyan',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Keamanan',
        'keterangan' => 'Bulanan',
        'nominal' => 25000,
    ]);

    $pembayaran = Pembayaran::create([
        'warga_id' => $wargaOther->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 25000,
        'keterangan' => 'Tagihan',
        'status' => 'pending',
        'invoice' => 'INV-TEST-004',
    ]);

    $user = User::factory()->create([
        'role' => 'user',
        'warga_id' => $wargaOwner->id,
    ]);

    $this->actingAs($user)
        ->get(route('pembayaran.pay', $pembayaran->id))
        ->assertForbidden();
});

it('marks pembayaran as paid on capture callback with accepted fraud status', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Fani',
        'nik' => '6666666666666666',
        'alamat' => 'Jl. Teratai',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Kebersihan',
        'keterangan' => 'Bulanan',
        'nominal' => 18000,
    ]);

    $invoice = 'INV-TEST-005';

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 18000,
        'keterangan' => 'Tagihan',
        'status' => 'pending',
        'invoice' => $invoice,
    ]);

    $signature = makeMidtransSignature($invoice, '200', '18000', 'test-server-key');

    $this->postJson(route('midtrans.callback'), [
        'order_id' => $invoice,
        'status_code' => '200',
        'gross_amount' => '18000',
        'transaction_status' => 'capture',
        'fraud_status' => 'accept',
        'signature_key' => $signature,
    ])->assertOk();

    expect($pembayaran->fresh()->status)->toBe('paid');
});

it('keeps pembayaran pending for expire cancel and deny callbacks', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Gilang',
        'nik' => '7777777777777777',
        'alamat' => 'Jl. Dahlia',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Lampu',
        'keterangan' => 'Bulanan',
        'nominal' => 22000,
    ]);

    foreach (['expire', 'cancel', 'deny'] as $index => $transactionStatus) {
        $invoice = 'INV-TEST-006-' . $index;

        $pembayaran = Pembayaran::create([
            'warga_id' => $warga->id,
            'jenis_pembayaran_id' => $jenis->id,
            'tanggal_bayar' => now()->toDateString(),
            'jumlah' => 22000,
            'keterangan' => 'Tagihan',
            'status' => 'pending',
            'invoice' => $invoice,
        ]);

        $signature = makeMidtransSignature($invoice, '200', '22000', 'test-server-key');

        $this->postJson(route('midtrans.callback'), [
            'order_id' => $invoice,
            'status_code' => '200',
            'gross_amount' => '22000',
            'transaction_status' => $transactionStatus,
            'signature_key' => $signature,
        ])->assertOk();

        expect($pembayaran->fresh()->status)->toBe('pending');
    }
});

it('handles repeated settlement callback idempotently', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Hana',
        'nik' => '8888888888888888',
        'alamat' => 'Jl. Cendana',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Sosial',
        'keterangan' => 'Bulanan',
        'nominal' => 27000,
    ]);

    $invoice = 'INV-TEST-007';

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 27000,
        'keterangan' => 'Tagihan',
        'status' => 'pending',
        'invoice' => $invoice,
    ]);

    $payload = [
        'order_id' => $invoice,
        'status_code' => '200',
        'gross_amount' => '27000',
        'transaction_status' => 'settlement',
        'signature_key' => makeMidtransSignature($invoice, '200', '27000', 'test-server-key'),
    ];

    $this->postJson(route('midtrans.callback'), $payload)->assertOk();
    $this->postJson(route('midtrans.callback'), $payload)->assertOk();

    expect($pembayaran->fresh()->status)->toBe('paid');
});

it('keeps pembayaran pending on capture callback with non-accept fraud status', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Indra',
        'nik' => '9999999999999999',
        'alamat' => 'Jl. Cemara',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Infrastruktur',
        'keterangan' => 'Bulanan',
        'nominal' => 35000,
    ]);

    $invoice = 'INV-TEST-008';

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 35000,
        'keterangan' => 'Tagihan',
        'status' => 'pending',
        'invoice' => $invoice,
    ]);

    $signature = makeMidtransSignature($invoice, '200', '35000', 'test-server-key');

    $this->postJson(route('midtrans.callback'), [
        'order_id' => $invoice,
        'status_code' => '200',
        'gross_amount' => '35000',
        'transaction_status' => 'capture',
        'fraud_status' => 'challenge',
        'signature_key' => $signature,
    ])->assertOk();

    expect($pembayaran->fresh()->status)->toBe('pending');
});

it('keeps pembayaran pending on capture callback with deny fraud status', function () {
    config(['services.midtrans.server_key' => 'test-server-key']);

    $warga = Warga::create([
        'nama' => 'Joko',
        'nik' => '1010101010101010',
        'alamat' => 'Jl. Pinus',
    ]);

    $jenis = JenisPembayaran::create([
        'nama' => 'Iuran Kesehatan',
        'keterangan' => 'Bulanan',
        'nominal' => 28000,
    ]);

    $invoice = 'INV-TEST-009';

    $pembayaran = Pembayaran::create([
        'warga_id' => $warga->id,
        'jenis_pembayaran_id' => $jenis->id,
        'tanggal_bayar' => now()->toDateString(),
        'jumlah' => 28000,
        'keterangan' => 'Tagihan',
        'status' => 'pending',
        'invoice' => $invoice,
    ]);

    $signature = makeMidtransSignature($invoice, '200', '28000', 'test-server-key');

    $this->postJson(route('midtrans.callback'), [
        'order_id' => $invoice,
        'status_code' => '200',
        'gross_amount' => '28000',
        'transaction_status' => 'capture',
        'fraud_status' => 'deny',
        'signature_key' => $signature,
    ])->assertOk();

    expect($pembayaran->fresh()->status)->toBe('pending');
});
