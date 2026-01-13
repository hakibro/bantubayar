<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenangananKesanggupan extends Model
{
    protected $table = 'penanganan_kesanggupan';

    protected $fillable = [
        'penanganan_id',
        'tanggal',
        'nominal',
    ];

    public function penanganan()
    {
        return $this->belongsTo(Penanganan::class);
    }
}
