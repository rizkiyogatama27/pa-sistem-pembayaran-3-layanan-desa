<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update event Renovasi Masjid dengan cover_image_url yang benar
$event = App\Models\EventDonasi::find(1);
if ($event && $event->cover_image_url === null) {
    // File di storage adalah: HJ7QsGI4ESntt5lRgyPGKMAmhPjlE03tykqmsO93.jpg
    $event->update(['cover_image_url' => 'HJ7QsGI4ESntt5lRgyPGKMAmhPjlE03tykqmsO93.jpg']);
    echo "✓ Event [1] Renovasi Masjid updated dengan cover image\n";
} else {
    echo "Event tidak ditemukan atau sudah punya cover_image_url\n";
}

// Verify
$updated = App\Models\EventDonasi::find(1);
echo "\nStatus sekarang:\n";
echo "  Nama: " . $updated->nama_event . "\n";
echo "  Cover Image URL: " . ($updated->cover_image_url ?? 'NULL') . "\n";
echo "  Slug: " . $updated->slug . "\n";
