<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('donation_payments') && ! Schema::hasColumn('donation_payments', 'is_anonymous')) {
            Schema::table('donation_payments', function (Blueprint $table) {
                $table->boolean('is_anonymous')->default(false)->after('warga_id');
            });
        }

        if (Schema::hasTable('event_donasi_kontribusis') && ! Schema::hasColumn('event_donasi_kontribusis', 'is_anonymous')) {
            Schema::table('event_donasi_kontribusis', function (Blueprint $table) {
                $table->boolean('is_anonymous')->default(false)->after('warga_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('donation_payments') && Schema::hasColumn('donation_payments', 'is_anonymous')) {
            Schema::table('donation_payments', function (Blueprint $table) {
                $table->dropColumn('is_anonymous');
            });
        }

        if (Schema::hasTable('event_donasi_kontribusis') && Schema::hasColumn('event_donasi_kontribusis', 'is_anonymous')) {
            Schema::table('event_donasi_kontribusis', function (Blueprint $table) {
                $table->dropColumn('is_anonymous');
            });
        }
    }
};