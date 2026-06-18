<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wargas = App\Models\Warga::all();
echo "Total warga: " . $wargas->count() . "\n";
foreach($wargas as $w) {
    $linked = App\Models\User::where('warga_id', $w->id)->first();
    echo "Warga ID=" . $w->id . " Nama=" . $w->nama . " NIK=" . $w->nik . " -> Linked to: " . ($linked ? $linked->email : 'BELUM') . "\n";
}

echo "\nSemua users:\n";
$users = App\Models\User::all();
foreach($users as $u) {
    echo "User: " . $u->email . " | role=" . $u->role . " | warga_id=" . $u->warga_id . " | status=" . $u->verification_status . "\n";
}
