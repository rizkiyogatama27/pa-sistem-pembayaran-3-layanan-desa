<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'rizkiyoga2005@gmail.com')->first();
if (! $user) {
    echo "User not found\n";
    exit(0);
}

$svc = new App\Services\AutoGenerateTagihanService();
$n = $svc->generateForUser($user);
echo "generated: {$n}\n";
