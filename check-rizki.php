<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check user Rizki status
$rizki = App\Models\User::where('email', 'rizkiyoga2005@gmail.com')->first();
echo "Rizki Status: " . ($rizki ? "email=" . $rizki->email . " warga_id=" . $rizki->warga_id . " verification_status=" . $rizki->verification_status : "NOT FOUND") . "\n";

// Check pembayaran for Rizki's warga
if ($rizki && $rizki->warga_id) {
    $pembayarans = App\Models\Pembayaran::where('warga_id', $rizki->warga_id)->get();
    echo "Pembayaran untuk warga Rizki: " . $pembayarans->count() . "\n";
    foreach ($pembayarans as $p) {
        echo "  - ID=" . $p->id . " Amount=" . $p->jumlah . " Status=" . $p->status . "\n";
    }
}
