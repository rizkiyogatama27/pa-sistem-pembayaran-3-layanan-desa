<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warga extends Model
{
    use HasFactory;
    protected $table = 'wargas';

    protected $fillable = [
        'keluarga_id',
        'nama',
        'nik',
        'alamat',
        'no_hp',
        'status',
    ];

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    /**
     * Get the user associated with the warga.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}