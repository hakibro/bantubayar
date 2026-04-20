<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaPembayaran extends Model
{
    protected $table = "siswa_pembayaran";
    protected $fillable = [
        'siswa_id',
        'periode',
        'kelas_info',
        'summary',
        'data',
        'is_lunas',
    ];

    protected $casts = [
        'data' => 'array',
        'is_lunas' => 'boolean',
        'summary' => 'array',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
