<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            if (! Schema::hasColumn('meter_readings', 'status')) {
                $table->string('status')->default('pending')->after('reading_source');
            }
            if (! Schema::hasColumn('meter_readings', 'photo_hash')) {
                $table->string('photo_hash')->nullable()->after('meter_photo');
            }
            if (! Schema::hasColumn('meter_readings', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('photo_hash');
            }
            if (! Schema::hasColumn('meter_readings', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
            if (! Schema::hasColumn('meter_readings', 'device_fingerprint')) {
                $table->string('device_fingerprint')->nullable()->after('lng');
            }
            if (! Schema::hasColumn('meter_readings', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meter_readings', function (Blueprint $table) {
            $table->dropColumn(['status', 'photo_hash', 'lat', 'lng', 'device_fingerprint', 'rejection_reason']);
        });
    }
};
