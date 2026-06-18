<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$events = App\Models\EventDonasi::all();
echo "Event Donasi di Database:\n";
foreach ($events as $e) {
    echo "  [" . $e->id . "] " . $e->nama_event . "\n";
    echo "      cover_image_url: " . ($e->cover_image_url ?? 'NULL') . "\n";
    echo "      slug: " . ($e->slug ?? 'NULL') . "\n";
    echo "      status: " . ($e->status ?? 'NULL') . "\n";
    echo "\n";
}

// Check storage folder
echo "\n=== Storage Folder ===\n";
$storagePath = 'storage/app/public/event-covers';
if (is_dir($storagePath)) {
    $files = scandir($storagePath);
    echo "Files in storage/app/public/event-covers:\n";
    foreach ($files as $f) {
        if ($f !== '.' && $f !== '..') {
            echo "  - " . $f . " (" . filesize($storagePath . '/' . $f) . " bytes)\n";
        }
    }
} else {
    echo "Folder tidak ada: " . $storagePath . "\n";
}
