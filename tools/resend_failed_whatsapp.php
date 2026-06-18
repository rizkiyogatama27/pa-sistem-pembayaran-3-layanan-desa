<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WhatsAppReminderLog;
use App\Services\WhatsAppService;

$service = app(WhatsAppService::class);
$logs = WhatsAppReminderLog::where('status', 'failed')->orderBy('id')->get();

if ($logs->isEmpty()) {
    echo "No failed logs to resend\n";
    exit(0);
}

foreach ($logs as $log) {
    $recipient = (string) $log->recipient;
    $message = (string) $log->message;

    echo "Resending log ID: {$log->id} to {$recipient}... ";
    $sent = false;
    try {
        $sent = $service->send($recipient, $message);
    } catch (\Throwable $e) {
        echo "error: " . $e->getMessage() . "\n";
        continue;
    }

    $last = $service->getLastResponse();

    if ($sent) {
        $log->status = 'sent';
        $log->sent_at = now();
        $log->error_message = null;
        $log->save();
        echo "sent\n";
    } else {
        $log->error_message = $last ? json_encode($last) : 'unknown error';
        $log->save();
        echo "failed (see log)\n";
    }
}
