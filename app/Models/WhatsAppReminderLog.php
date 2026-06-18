<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppReminderLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_reminder_logs';

    protected $fillable = [
        'pembayaran_id',
        'warga_id',
        'recipient',
        'status',
        'message',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }
}
