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
    ];

    protected $casts = [
        'data' => 'json',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
