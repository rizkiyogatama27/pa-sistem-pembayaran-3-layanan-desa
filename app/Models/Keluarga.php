<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Keluarga extends Model
{
    use HasFactory;

    protected $table = 'keluargas';

    protected $fillable = [
        'no_kk',
        'nama_keluarga',
        'alamat',
    ];

    public function wargas(): HasMany
    {
        return $this->hasMany(Warga::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
