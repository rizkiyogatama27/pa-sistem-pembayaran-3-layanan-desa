<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo 'provider=' . config('services.whatsapp.provider') . PHP_EOL;
echo 'token=' . (config('services.whatsapp.token') ? 'set' : 'missing') . PHP_EOL;
echo 'phone_number_id=' . (config('services.whatsapp.phone_number_id') ? 'set' : 'missing') . PHP_EOL;
echo 'twilio_sid=' . (config('services.twilio.account_sid') ? 'set' : 'missing') . PHP_EOL;
echo 'twilio_auth=' . (config('services.twilio.auth_token') ? 'set' : 'missing') . PHP_EOL;
echo 'twilio_from=' . (config('services.twilio.whatsapp_from') ? 'set' : 'missing') . PHP_EOL;
