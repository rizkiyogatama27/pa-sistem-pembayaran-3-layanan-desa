<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Pembayaran Status Check ===\n";

// Check Rizki's pembayaran status
$wargaRizki = App\Models\Warga::where('nik', '3201010101010102')->first();
if ($wargaRizki) {
    $pembayarans = App\Models\Pembayaran::where('warga_id', $wargaRizki->id)->get();
    echo "Rizki's Pembayaran Status:\n";
    foreach ($pembayarans as $p) {
        echo "  [" . $p->id . "] " . $p->jenisPembayaran->nama . " | Rp " . number_format($p->jumlah) . " | Status=" . $p->status . " | Invoice=" . ($p->invoice ?? 'NULL') . " | Bayar=" . ($p->tanggal_bayar ?? 'NULL') . "\n";
    }
    
    // Option untuk manual update
    echo "\n=== Manual Update ===\n";
    echo "Ketik ID pembayaran yang mau diupdate ke 'paid' (misal: 11): ";
    $input = trim(fgets(STDIN));
    
    if ($input && is_numeric($input)) {
        $pembayaran = App\Models\Pembayaran::find($input);
        if ($pembayaran && $pembayaran->warga_id == $wargaRizki->id) {
            $pembayaran->status = 'paid';
            $pembayaran->tanggal_bayar = now();
            $pembayaran->save();
            echo "✓ Pembayaran ID " . $input . " updated to PAID\n";
        } else {
            echo "✗ Pembayaran ID " . $input . " tidak ditemukan atau bukan milik Rizki\n";
        }
    }
    
} else {
    echo "Warga Rizki tidak ditemukan\n";
}
