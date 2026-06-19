<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventDonasi extends Model
{
    use HasFactory;

    protected $table = 'event_donasis';

    protected $fillable = [
        'nama_event',
        'slug',
        'tujuan',
        'cover_image_url',
        'target_dana',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    protected $casts = [
        'target_dana' => 'integer',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function kontribusis(): HasMany
    {
        return $this->hasMany(EventDonasiKontribusi::class, 'event_donasi_id');
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        $coverImage = $this->attributes['cover_image_url'] ?? null;

        if (!empty($coverImage)) {
            return asset('images/event-covers/' . ltrim($coverImage, '/'));
        }

        $legacyCoverImage = $this->attributes['cover_image'] ?? null;

        if (!empty($legacyCoverImage)) {
            return asset('images/event-covers/' . ltrim($legacyCoverImage, '/'));
        }

        return null;
    }
}
