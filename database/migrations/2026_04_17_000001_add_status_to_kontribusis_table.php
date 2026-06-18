<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('event_donasi_kontribusis') || Schema::hasColumn('event_donasi_kontribusis', 'status')) {
            return;
        }

        Schema::table('event_donasi_kontribusis', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('jumlah');
        });
    }

    public function down()
    {
        if (! Schema::hasTable('event_donasi_kontribusis') || ! Schema::hasColumn('event_donasi_kontribusis', 'status')) {
            return;
        }

        Schema::table('event_donasi_kontribusis', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
