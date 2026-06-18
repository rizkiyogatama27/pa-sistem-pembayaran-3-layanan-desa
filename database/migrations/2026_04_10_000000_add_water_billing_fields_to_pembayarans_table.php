<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            if (! Schema::hasColumn('pembayarans', 'periode')) {
                $table->string('periode')->nullable()->after('tanggal_bayar');
            }

            if (! Schema::hasColumn('pembayarans', 'meter_awal')) {
                $table->integer('meter_awal')->nullable()->after('periode');
            }

            if (! Schema::hasColumn('pembayarans', 'meter_akhir')) {
                $table->integer('meter_akhir')->nullable()->after('meter_awal');
            }

            if (! Schema::hasColumn('pembayarans', 'pemakaian_air')) {
                $table->integer('pemakaian_air')->nullable()->after('meter_akhir');
            }

            if (! Schema::hasColumn('pembayarans', 'tarif_per_meter')) {
                $table->integer('tarif_per_meter')->default(1500)->after('pemakaian_air');
            }

            if (! Schema::hasColumn('pembayarans', 'biaya_tetap')) {
                $table->integer('biaya_tetap')->default(5000)->after('tarif_per_meter');
            }

            if (! Schema::hasColumn('pembayarans', 'denda')) {
                $table->integer('denda')->default(0)->after('biaya_tetap');
            }

            if (! Schema::hasColumn('pembayarans', 'jatuh_tempo')) {
                $table->date('jatuh_tempo')->nullable()->after('denda');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $columns = [
                'periode',
                'meter_awal',
                'meter_akhir',
                'pemakaian_air',
                'tarif_per_meter',
                'biaya_tetap',
                'denda',
                'jatuh_tempo',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pembayarans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};