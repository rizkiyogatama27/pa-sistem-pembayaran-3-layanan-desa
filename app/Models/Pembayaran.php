<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'warga_id',
        'jenis_pembayaran_id',
        'tanggal_bayar',
        'periode',
        'meter_awal',
        'meter_akhir',
        'pemakaian_air',
        'tarif_per_meter',
        'biaya_tetap',
        'denda',
        'jatuh_tempo',
        'jumlah',
        'keterangan',
        'status',
        'payment_method',
        'cash_received_amount',
        'cash_change_amount',
        'paid_by_user_id',
        'invoice',
        'last_whatsapp_reminder_at',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'jatuh_tempo' => 'date',
        'cash_received_amount' => 'integer',
        'cash_change_amount' => 'integer',
        'last_whatsapp_reminder_at' => 'date',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class);
    }

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class);
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }
}