<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Hapus semua pembayaran yang sudah ada untuk Rizki
$wargaRizki = App\Models\Warga::where('nik', '3201010101010102')->first();
if ($wargaRizki) {
    App\Models\Pembayaran::where('warga_id', $wargaRizki->id)->delete();
    echo "Pembayaran lama dihapus.\n";
}

// Buat atau cari jenis pembayaran
$jenisSampah = App\Models\JenisPembayaran::firstOrCreate(['nama' => 'Sampah'], ['deskripsi' => 'Retribusi Sampah']);
$jenisAir = App\Models\JenisPembayaran::firstOrCreate(['nama' => 'Air'], ['deskripsi' => 'Tagihan Air Bersih']);
$jenisTest = App\Models\JenisPembayaran::firstOrCreate(['nama' => 'Test Payment'], ['deskripsi' => 'Test Payment Gateway']);

// Hitung jatuh tempo berdasarkan periode (akhir bulan)
$jatuhTempo = \Illuminate\Support\Carbon::createFromFormat('Y-m', '2026-05')->endOfMonth()->format('Y-m-d');

// Pembayaran Sampah Mei 2026 (fixed 10000)
App\Models\Pembayaran::create([
    'warga_id' => $wargaRizki->id,
    'jenis_pembayaran_id' => $jenisSampah->id,
    'periode' => '2026-05',
    'meter_awal' => null,
    'meter_akhir' => null,
    'pemakaian_air' => null,
    'tarif_per_meter' => 1500,
    'biaya_tetap' => 10000,
    'denda' => 0,
    'jumlah' => 10000,
    'tanggal_bayar' => null,
    'jatuh_tempo' => $jatuhTempo,
]);

// Pembayaran Air Mei 2026 (dihitung dari meter)
// Formula: (meter_akhir - meter_awal) * tarif_per_meter + biaya_tetap + denda
$meter_awal = 120;
$meter_akhir = 145;
$pemakaian = $meter_akhir - $meter_awal; // 25 meter
$tarif = 1500;
$biaya_tetap = 25000;
$denda = 0;
$total = ($pemakaian * $tarif) + $biaya_tetap + $denda; // (25*1500) + 25000 = 62500

App\Models\Pembayaran::create([
    'warga_id' => $wargaRizki->id,
    'jenis_pembayaran_id' => $jenisAir->id,
    'periode' => '2026-05',
    'meter_awal' => $meter_awal,
    'meter_akhir' => $meter_akhir,
    'pemakaian_air' => $pemakaian,
    'tarif_per_meter' => $tarif,
    'biaya_tetap' => $biaya_tetap,
    'denda' => $denda,
    'jumlah' => $total,
    'tanggal_bayar' => null,
    'jatuh_tempo' => $jatuhTempo,
]);

// Test Payment Rp 500 untuk testing payment gateway
App\Models\Pembayaran::create([
    'warga_id' => $wargaRizki->id,
    'jenis_pembayaran_id' => $jenisTest->id,
    'periode' => '2026-05-test',
    'jumlah' => 500,
    'tanggal_bayar' => null,
    'keterangan' => 'Test payment gateway - apakah production atau sandbox',
    'jatuh_tempo' => now()->addDays(7)->format('Y-m-d'),
]);

echo "Pembayaran untuk Rizki berhasil dibuat!\n";
$pembayarans = App\Models\Pembayaran::where('warga_id', $wargaRizki->id)->get();
foreach($pembayarans as $p) {
    echo "  - " . $p->jenisPembayaran->nama . ": Rp " . number_format($p->jumlah) . "\n";
}


