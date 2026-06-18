<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            if (! Schema::hasColumn('pembayarans', 'last_whatsapp_reminder_at')) {
                $table->date('last_whatsapp_reminder_at')->nullable()->after('invoice');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            if (Schema::hasColumn('pembayarans', 'last_whatsapp_reminder_at')) {
                $table->dropColumn('last_whatsapp_reminder_at');
            }
        });
    }
};