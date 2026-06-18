<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Reset invoice untuk Rizki
$wargaRizki = App\Models\Warga::where('nik', '3201010101010102')->first();
if ($wargaRizki) {
    $pembayarans = App\Models\Pembayaran::where('warga_id', $wargaRizki->id)->update(['invoice' => null]);
    echo "Invoice untuk Rizki reset: " . $pembayarans . " pembayaran\n";
} else {
    echo "Warga Rizki tidak ditemukan\n";
}
