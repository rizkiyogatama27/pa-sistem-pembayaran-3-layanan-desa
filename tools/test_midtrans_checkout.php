<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\Pembayaran::where('status', '!=', 'paid')->where('jumlah', '>', 0)->latest('id')->first();
if (! $p) {
    echo "no unpaid payment\n";
    exit;
}

try {
    $driver = app(App\Services\PaymentGateways\PaymentGatewayManager::class)->driver();
    $checkout = $driver->createCheckout($p);
    echo json_encode($checkout, JSON_PRETTY_PRINT) . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
