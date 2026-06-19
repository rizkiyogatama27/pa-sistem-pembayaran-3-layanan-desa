<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Starting data migration...\n";

// Disable foreign key checks on tidb
DB::connection('tidb')->statement('SET FOREIGN_KEY_CHECKS=0;');

$tables = DB::connection('mysql')->select('SHOW TABLES');
$dbName = 'sim_pembayaran_desa';
$tableKey = "Tables_in_$dbName";

foreach ($tables as $table) {
    $tableName = array_values(get_object_vars($table))[0];
    if ($tableName === 'migrations') continue;
    
    echo "Copying table $tableName...\n";
    $records = DB::connection('mysql')->table($tableName)->get()->map(fn($item) => (array)$item)->toArray();
    
    if (count($records) > 0) {
        $chunks = array_chunk($records, 100);
        foreach ($chunks as $chunk) {
            DB::connection('tidb')->table($tableName)->insert($chunk);
        }
        echo "Copied " . count($records) . " rows.\n";
    }
}

DB::connection('tidb')->statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Migration completed successfully!\n";
