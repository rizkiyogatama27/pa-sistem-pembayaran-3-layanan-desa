<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$svc = $app->make(App\Services\WhatsAppService::class);
$recipient = $svc->normalizeNumber('085230236462');
$message = 'Tes notifikasi dari Sistem PA. Jika menerima, balas OK.';
$sent = $svc->send($recipient, $message);
echo 'SENT=' . ($sent ? '1' : '0') . PHP_EOL;
$resp = $svc->getLastResponse();
echo 'RESPONSE=' . json_encode($resp) . PHP_EOL;
