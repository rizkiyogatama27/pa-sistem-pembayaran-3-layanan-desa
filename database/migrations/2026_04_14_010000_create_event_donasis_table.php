<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_donasis')) {
            return;
        }

        Schema::create('event_donasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama_event');
            $table->string('slug')->unique();
            $table->text('tujuan');
            $table->bigInteger('target_dana')->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_donasis');
    }
};
