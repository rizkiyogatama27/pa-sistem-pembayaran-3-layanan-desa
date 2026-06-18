<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wargas', function (Blueprint $table) {
            if (! Schema::hasColumn('wargas', 'no_hp')) {
                $table->string('no_hp')->nullable()->after('alamat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wargas', function (Blueprint $table) {
            if (Schema::hasColumn('wargas', 'no_hp')) {
                $table->dropColumn('no_hp');
            }
        });
    }
};