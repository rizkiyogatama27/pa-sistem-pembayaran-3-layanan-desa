<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            if (! Schema::hasColumn('meter_readings', 'ocr_engine')) {
                $table->string('ocr_engine')->nullable()->after('reading_source');
            }

            if (! Schema::hasColumn('meter_readings', 'ocr_status')) {
                $table->string('ocr_status')->nullable()->after('ocr_engine');
            }

            if (! Schema::hasColumn('meter_readings', 'ocr_text')) {
                $table->text('ocr_text')->nullable()->after('ocr_status');
            }

            if (! Schema::hasColumn('meter_readings', 'ocr_meter_akhir')) {
                $table->integer('ocr_meter_akhir')->nullable()->after('ocr_text');
            }

            if (! Schema::hasColumn('meter_readings', 'ocr_confidence')) {
                $table->decimal('ocr_confidence', 5, 2)->nullable()->after('ocr_meter_akhir');
            }

            if (! Schema::hasColumn('meter_readings', 'ocr_error')) {
                $table->text('ocr_error')->nullable()->after('ocr_confidence');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropColumn([
                'ocr_engine',
                'ocr_status',
                'ocr_text',
                'ocr_meter_akhir',
                'ocr_confidence',
                'ocr_error',
            ]);
        });
    }
};
