<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Reset tanggal_bayar untuk Sampah dan Air, tapi jangan yang sudah paid
App\Models\Pembayaran::where('id', 9)->update(['tanggal_bayar' => null]);
App\Models\Pembayaran::where('id', 10)->update(['tanggal_bayar' => null]);

echo "Pembayaran Sampah dan Air di-reset tanggal_bayar ke NULL\n";

$wargaRizki = App\Models\Warga::where('nik', '3201010101010102')->first();
$pembayarans = App\Models\Pembayaran::where('warga_id', $wargaRizki->id)->get();
echo "\nStatus sekarang:\n";
foreach ($pembayarans as $p) {
    echo "  [" . $p->id . "] " . $p->jenisPembayaran->nama . " | Rp " . number_format($p->jumlah) . " | Status=" . $p->status . " | Bayar=" . ($p->tanggal_bayar ?? 'NULL') . "\n";
}
