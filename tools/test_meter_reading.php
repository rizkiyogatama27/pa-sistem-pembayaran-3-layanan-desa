<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Warga;
use App\Models\Keluarga;
use App\Models\JenisPembayaran;
use App\Models\Pembayaran;
use App\Models\MeterReading;
use App\Services\AutoGenerateTagihanService;

// 1) find or create warga
$warga = Warga::first();
if (! $warga) {
    $kel = Keluarga::first();
    if (! $kel) {
        $kel = Keluarga::create([
            'no_kk' => 'AUTO-' . time(),
            'nama_keluarga' => 'Keluarga Test',
            'alamat' => '-',
        ]);
    }

    $warga = Warga::create([
        'nik' => '0000000000000000',
        'nama' => 'Warga Test',
        'alamat' => '-',
        'keluarga_id' => $kel->id,
    ]);
}

// 2) find or create jenis pembayaran air
$jenis = JenisPembayaran::whereRaw("LOWER(nama) like ?", ['%air%'])->first();
if (! $jenis) {
    $jenis = JenisPembayaran::create([
        'nama' => 'Air Test',
        'nominal' => 0,
    ]);
}

// 3) create a draft pembayaran for this warga and jenis
// reuse existing pembayaran for same warga/jenis/periode if exists
$pembayaran = Pembayaran::where('warga_id', $warga->id)
    ->where('jenis_pembayaran_id', $jenis->id)
    ->where('periode', date('Y-m'))
    ->first();

if (! $pembayaran) {
    $pembayaran = Pembayaran::create([
    'warga_id' => $warga->id,
    'jenis_pembayaran_id' => $jenis->id,
    'tanggal_bayar' => date('Y-m-d'),
    'periode' => date('Y-m'),
    'status' => 'pending',
    'jatuh_tempo' => date('Y-m-d', strtotime('+10 days')),
    'invoice' => 'INV-TEST-' . time(),
    'meter_awal' => 0,
    'meter_akhir' => null,
    'pemakaian_air' => 0,
    'tarif_per_meter' => 1500,
    'biaya_tetap' => 5000,
    'denda' => 0,
    'jumlah' => 0,
    'keterangan' => 'Draft test',
    ]);
}

// 4) simulate petugas input meter reading
$meterAkhir = 125;

$reading = MeterReading::create([
    'pembayaran_id' => $pembayaran->id,
    'warga_id' => $warga->id,
    'meter_awal' => $pembayaran->meter_awal,
    'meter_akhir' => $meterAkhir,
    'meter_photo' => null,
    'reading_at' => date('Y-m-d H:i:s'),
    'reading_source' => 'petugas',
    'verified_by' => null,
    'notes' => 'Test reading',
]);

// 5) calculate bill using service
$service = app(AutoGenerateTagihanService::class);
$calc = $service->calculateWaterBill((int)$reading->meter_awal, (int)$reading->meter_akhir, (int)$pembayaran->tarif_per_meter, (int)$pembayaran->biaya_tetap, 0);

$pembayaran->meter_awal = $reading->meter_awal;
$pembayaran->meter_akhir = $reading->meter_akhir;
$pembayaran->pemakaian_air = $calc['usage'];
$pembayaran->jumlah = $calc['amount'];
$pembayaran->keterangan = 'Auto test update: pemakaian ' . $calc['usage'];
$pembayaran->save();

// output results
echo "Pembayaran ID: " . $pembayaran->id . PHP_EOL;
echo "Meter awal: " . $pembayaran->meter_awal . PHP_EOL;
echo "Meter akhir: " . $pembayaran->meter_akhir . PHP_EOL;
echo "Pemakaian: " . $pembayaran->pemakaian_air . PHP_EOL;
echo "Jumlah: " . $pembayaran->jumlah . PHP_EOL;

echo "MeterReading ID: " . $reading->id . PHP_EOL;

echo "Done" . PHP_EOL;
