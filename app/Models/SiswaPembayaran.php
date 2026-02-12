<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaPembayaran extends Model
{
    protected $table = "siswa_pembayaran";
    protected $fillable = [
        'siswa_id',
        'periode',
        'data',
        'is_lunas',
    ];

    protected $casts = [
        'data' => 'json',
        'is_lunas' => 'boolean',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
