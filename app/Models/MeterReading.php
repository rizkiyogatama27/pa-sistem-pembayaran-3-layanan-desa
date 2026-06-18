<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    use HasFactory;

    protected $table = 'meter_readings';

    protected $fillable = [
        'pembayaran_id',
        'warga_id',
        'meter_awal',
        'meter_akhir',
        'meter_photo',
        'photo_hash',
        'lat',
        'lng',
        'device_fingerprint',
        'ocr_engine',
        'ocr_status',
        'ocr_text',
        'ocr_meter_akhir',
        'ocr_confidence',
        'ocr_error',
        'reading_at',
        'reading_source',
        'verified_by',
        'status',
        'rejection_reason',
        'notes',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }
}
