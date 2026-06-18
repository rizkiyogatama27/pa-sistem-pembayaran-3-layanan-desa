<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('event_donasis')) {
            return;
        }

        if (!Schema::hasColumn('event_donasis', 'cover_image_url')) {
            Schema::table('event_donasis', function (Blueprint $table) {
                $table->string('cover_image_url')->nullable()->after('tujuan');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('event_donasis')) {
            return;
        }

        if (Schema::hasColumn('event_donasis', 'cover_image_url')) {
            Schema::table('event_donasis', function (Blueprint $table) {
                $table->dropColumn('cover_image_url');
            });
        }
    }
};
