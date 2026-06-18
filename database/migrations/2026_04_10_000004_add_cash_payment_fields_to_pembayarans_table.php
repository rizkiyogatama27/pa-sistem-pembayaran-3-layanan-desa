<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');
            $table->unsignedBigInteger('cash_received_amount')->nullable()->after('payment_method');
            $table->unsignedBigInteger('cash_change_amount')->nullable()->after('cash_received_amount');
            $table->foreignId('paid_by_user_id')->nullable()->after('cash_change_amount')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            $table->dropForeign(['paid_by_user_id']);
            $table->dropColumn(['payment_method', 'cash_received_amount', 'cash_change_amount', 'paid_by_user_id']);
        });
    }
};
