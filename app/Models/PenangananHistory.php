<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenangananHistory extends Model
{
    protected $table = 'penanganan_history';
    protected $touches = ['penanganan'];

    protected $fillable = [
        'penanganan_id',
        'jenis_penanganan',
        'catatan',
    ];

    public function penanganan()
    {
        return $this->belongsTo(Penanganan::class);
    }
}
