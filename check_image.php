<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$event = Illuminate\Support\Facades\DB::connection('tidb')->table('event_donasis')->first();
echo $event->cover_image_url;
