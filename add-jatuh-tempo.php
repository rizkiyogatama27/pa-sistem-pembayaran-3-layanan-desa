<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update jatuh_tempo untuk Sampah dan Air
$jatuhTempo = \Illuminate\Support\Carbon::createFromFormat('Y-m', '2026-05')->endOfMonth()->format('Y-m-d');

// Update ID 9 (Sampah) dan ID 10 (Air)
App\Models\Pembayaran::whereIn('id', [9, 10])->update(['jatuh_tempo' => $jatuhTempo]);

echo "Jatuh tempo updated: " . $jatuhTempo . "\n";

$wargaRizki = App\Models\Warga::where('nik', '3201010101010102')->first();
$pembayarans = App\Models\Pembayaran::where('warga_id', $wargaRizki->id)->get();
echo "\nStatus sekarang:\n";
foreach ($pembayarans as $p) {
    echo "  [" . $p->id . "] " . $p->jenisPembayaran->nama . " | Rp " . number_format($p->jumlah) . " | Status=" . $p->status . " | Jatuh Tempo=" . ($p->jatuh_tempo ?? 'NULL') . "\n";
}
