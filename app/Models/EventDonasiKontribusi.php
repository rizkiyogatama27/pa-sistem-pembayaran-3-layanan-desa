<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventDonasiKontribusi extends Model
{
    use HasFactory;

    protected $table = 'event_donasi_kontribusis';

    protected $fillable = [
        'event_donasi_id',
        'warga_id',
        'is_anonymous',
        'tanggal_donasi',
        'nominal',
        'metode',
        'status',
        'catatan',
        'invoice',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'tanggal_donasi' => 'date',
        'nominal' => 'integer',
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
