<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_donasi_kontribusis')) {
            return;
        }

        Schema::create('event_donasi_kontribusis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_donasi_id')->constrained('event_donasis')->cascadeOnDelete();
            $table->foreignId('warga_id')->nullable()->constrained('wargas')->nullOnDelete();
            $table->date('tanggal_donasi');
            $table->bigInteger('nominal');
            $table->string('metode')->nullable();
            $table->enum('status', ['pending', 'paid'])->default('paid');
            $table->text('catatan')->nullable();
            $table->string('invoice')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_donasi_kontribusis');
    }
};
