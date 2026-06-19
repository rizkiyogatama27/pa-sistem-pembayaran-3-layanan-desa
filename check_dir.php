<?php
$dir = __DIR__ . '/public/images/event-covers';
if (is_dir($dir)) {
    echo "Directory exists!\n";
    print_r(scandir($dir));
} else {
    echo "Directory DOES NOT EXIST!\n";
}
