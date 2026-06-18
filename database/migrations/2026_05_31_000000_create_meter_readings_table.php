<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('meter_readings')) {
            return;
        }

        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pembayaran_id')->nullable();
            $table->unsignedBigInteger('warga_id')->nullable();
            $table->integer('meter_awal')->nullable();
            $table->integer('meter_akhir')->nullable();
            $table->string('meter_photo')->nullable();
            $table->dateTime('reading_at')->nullable();
            $table->string('reading_source')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
