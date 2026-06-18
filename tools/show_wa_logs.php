<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WhatsAppReminderLog;

$logs = WhatsAppReminderLog::with('warga')->orderByDesc('id')->take(30)->get();

foreach ($logs as $log) {
    echo "ID: {$log->id}\n";
    echo "Warga: " . ($log->warga?->nama ?? 'N/A') . " (" . ($log->warga?->id ?? 'N/A') . ")\n";
    echo "Recipient: {$log->recipient}\n";
    echo "Status: {$log->status}\n";
    echo "Sent at: " . ($log->sent_at?->toDateTimeString() ?? 'null') . "\n";
    echo "Message: " . substr($log->message ?? '', 0, 200) . "\n";
    echo "Error: " . substr($log->error_message ?? '', 0, 400) . "\n";
    echo str_repeat('-', 60) . "\n";
}

if ($logs->isEmpty()) {
    echo "No logs found\n";
}
