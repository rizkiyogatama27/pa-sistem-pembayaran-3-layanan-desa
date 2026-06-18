<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('wargas') && ! Schema::hasColumn('wargas', 'keluarga_id')) {
            Schema::table('wargas', function (Blueprint $table) {
                $table->foreignId('keluarga_id')->nullable()->after('id')->constrained('keluargas')->nullOnDelete();
            });
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'keluarga_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('keluarga_id')->nullable()->after('warga_id')->constrained('keluargas')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('keluargas') || ! Schema::hasTable('wargas')) {
            return;
        }

        // Backfill data lama: setiap warga minimal terhubung ke satu keluarga.
        $wargas = DB::table('wargas')
            ->select('id', 'nama', 'alamat', 'keluarga_id')
            ->orderBy('id')
            ->get();

        foreach ($wargas as $warga) {
            if (! empty($warga->keluarga_id)) {
                continue;
            }

            $newKeluargaId = DB::table('keluargas')->insertGetId([
                'no_kk' => 'AUTO-' . str_pad((string) $warga->id, 8, '0', STR_PAD_LEFT),
                'nama_keluarga' => 'Keluarga ' . ($warga->nama ?? 'Warga'),
                'alamat' => $warga->alamat,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('wargas')
                ->where('id', $warga->id)
                ->update(['keluarga_id' => $newKeluargaId]);
        }

        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'warga_id') || ! Schema::hasColumn('users', 'keluarga_id')) {
            return;
        }

        $users = DB::table('users')
            ->select('id', 'warga_id')
            ->whereNotNull('warga_id')
            ->get();

        foreach ($users as $user) {
            $keluargaId = DB::table('wargas')->where('id', $user->warga_id)->value('keluarga_id');

            DB::table('users')
                ->where('id', $user->id)
                ->update(['keluarga_id' => $keluargaId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'keluarga_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('keluarga_id');
            });
        }

        if (Schema::hasTable('wargas') && Schema::hasColumn('wargas', 'keluarga_id')) {
            Schema::table('wargas', function (Blueprint $table) {
                $table->dropConstrainedForeignId('keluarga_id');
            });
        }
    }
};
