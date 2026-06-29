<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

config(['database.connections.mysql.host' => 'gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com']);
config(['database.connections.mysql.port' => '4000']);
config(['database.connections.mysql.database' => 'test']);
config(['database.connections.mysql.username' => 'MtcbV88ZauAGQQE.root']);
config(['database.connections.mysql.password' => 'ouex0ELR0iQJJoIH']);

\Illuminate\Support\Facades\DB::purge('mysql');
\Illuminate\Support\Facades\DB::reconnect('mysql');

\Illuminate\Support\Facades\DB::statement('ALTER TABLE meter_readings MODIFY meter_photo LONGTEXT');
echo "TiDB Schema modified successfully.\n";
