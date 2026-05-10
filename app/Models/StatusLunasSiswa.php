<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusLunasSiswa extends Model
{
    protected $table = 'v_status_lunas_siswa';

    public $timestamps = false;

    protected $casts = [
        'is_lunas' => 'integer',
        'total_tunggakan' => 'integer',
    ];
}
