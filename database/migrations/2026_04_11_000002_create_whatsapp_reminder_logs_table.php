<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->nullable()->constrained('pembayarans')->nullOnDelete();
            $table->foreignId('warga_id')->nullable()->constrained('wargas')->nullOnDelete();
            $table->string('recipient', 30)->nullable();
            $table->enum('status', ['sent', 'failed', 'skipped'])->default('sent');
            $table->text('message')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_reminder_logs');
    }
};
