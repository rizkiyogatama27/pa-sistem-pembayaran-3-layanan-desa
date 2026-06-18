<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::whereNotNull('warga_id')->first();
if (! $user) {
    echo "No user with warga_id found\n";
    exit(0);
}

echo "Testing for user: {$user->id} - {$user->email} (warga_id={$user->warga_id})\n";
$svc = new App\Services\AutoGenerateTagihanService();
$n = $svc->generateForUser($user);
echo "generated: {$n}\n";
