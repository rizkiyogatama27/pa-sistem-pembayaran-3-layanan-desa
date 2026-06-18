<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $indexName = 'pembayarans_warga_jenis_periode_unique';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('pembayarans')) {
            return;
        }

        if ($this->indexExists($this->indexName)) {
            return;
        }

        $duplicateCount = DB::table('pembayarans')
            ->selectRaw('warga_id, jenis_pembayaran_id, periode, COUNT(*) as total')
            ->whereNotNull('periode')
            ->groupBy('warga_id', 'jenis_pembayaran_id', 'periode')
            ->having('total', '>', 1)
            ->count();

        // Aman: hanya buat unique index jika tidak ada data duplikat historis.
        if ($duplicateCount > 0) {
            return;
        }

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->unique(['warga_id', 'jenis_pembayaran_id', 'periode'], $this->indexName);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('pembayarans')) {
            return;
        }

        if (! $this->indexExists($this->indexName)) {
            return;
        }

        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropUnique($this->indexName);
        });
    }

    private function indexExists(string $indexName): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('pembayarans')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $indexName) {
                    return true;
                }
            }

            return false;
        }

        $rows = DB::select('SHOW INDEX FROM pembayarans WHERE Key_name = ?', [$indexName]);

        return ! empty($rows);
    }
};
