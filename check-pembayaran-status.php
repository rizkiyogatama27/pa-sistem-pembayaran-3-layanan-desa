<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check Rizki's pembayaran status
$wargaRizki = App\Models\Warga::where('nik', '3201010101010102')->first();
if ($wargaRizki) {
    $pembayarans = App\Models\Pembayaran::where('warga_id', $wargaRizki->id)->get();
    echo "Rizki's Pembayaran Status:\n";
    foreach ($pembayarans as $p) {
        echo "  ID=" . $p->id . " | " . $p->jenisPembayaran->nama . " | Rp " . number_format($p->jumlah) . " | Status=" . $p->status . " | Invoice=" . ($p->invoice ?? 'NULL') . "\n";
    }
} else {
    echo "Warga Rizki tidak ditemukan\n";
}
