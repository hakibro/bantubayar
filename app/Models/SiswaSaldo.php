<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaSaldo extends Model
{
    protected $table = 'siswa_saldo';

    protected $fillable = [
        'siswa_id',
        'saldo',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
