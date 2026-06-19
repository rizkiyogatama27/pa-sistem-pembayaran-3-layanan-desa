<?php
$src = __DIR__ . '/storage/app/public/event-covers';
$dest = __DIR__ . '/public/images/event-covers';

if (!is_dir($dest)) {
    mkdir($dest, 0777, true);
}

$files = glob($src . '/*');
foreach ($files as $file) {
    if (is_file($file)) {
        copy($file, $dest . '/' . basename($file));
        echo "Copied " . basename($file) . "\n";
    }
}
echo "Done.\n";
