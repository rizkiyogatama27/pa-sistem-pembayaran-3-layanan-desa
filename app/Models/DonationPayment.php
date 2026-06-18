<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationPayment extends Model
{
    use HasFactory;

    protected $table = 'donation_payments';

    protected $fillable = [
        'event_donasi_id',
        'warga_id',
        'is_anonymous',
        'jumlah',
        'invoice',
        'status',
        'payment_method',
        'catatan',
        'tanggal_bayar',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'jumlah' => 'integer',
        'tanggal_bayar' => 'datetime',
    ];

    public function eventDonasi(): BelongsTo
    {
        return $this->belongsTo(EventDonasi::class, 'event_donasi_id');
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }
}
