<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\PembayaranController;


class JenisPembayaran extends Model
{
    use HasFactory;
    

    public function pembayarans()
    {
    return $this->hasMany(Pembayaran::class);
    }

    protected $fillable = [
    'nama',
    'keterangan',
    'nominal'
];
}