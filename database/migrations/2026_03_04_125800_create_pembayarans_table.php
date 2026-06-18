<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warga_id')
                ->constrained('wargas')
                ->cascadeOnDelete();

            $table->foreignId('jenis_pembayaran_id')
                ->constrained('jenis_pembayarans')
                ->cascadeOnDelete();

            $table->date('tanggal_bayar');
            $table->string('periode')->nullable();
            $table->integer('meter_awal')->nullable();
            $table->integer('meter_akhir')->nullable();
            $table->integer('pemakaian_air')->nullable();
            $table->integer('tarif_per_meter')->default(1500);
            $table->integer('biaya_tetap')->default(5000);
            $table->integer('denda')->default(0);
            $table->date('jatuh_tempo')->nullable();
            $table->integer('jumlah');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};