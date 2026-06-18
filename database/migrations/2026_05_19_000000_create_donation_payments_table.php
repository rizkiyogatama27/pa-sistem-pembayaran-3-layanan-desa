<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('donation_payments')) {
            return;
        }

        Schema::create('donation_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_donasi_id')->constrained('event_donasis')->cascadeOnDelete();
            $table->foreignId('warga_id')->nullable()->constrained('wargas')->nullOnDelete();
            $table->bigInteger('jumlah');
            $table->string('invoice')->nullable()->unique();
            $table->enum('status', ['pending', 'paid', 'expired'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->text('catatan')->nullable();
            $table->datetime('tanggal_bayar')->nullable();
            $table->timestamps();

            $table->index('event_donasi_id');
            $table->index('warga_id');
            $table->index('status');
            $table->index('invoice');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_payments');
    }
};
