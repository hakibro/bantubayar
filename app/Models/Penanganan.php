<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penanganan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penanganan';

    protected $fillable = [
        'id_siswa',
        'id_petugas',
        'jenis_pembayaran',
        'saldo',
        'jenis_penanganan',
        'catatan',
        'rating',
        'hasil',
        'status',
    ];

    protected $casts = [
        'jenis_pembayaran' => 'array',
        'rating' => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'id_petugas');
    }

    public function kesanggupan()
    {
        return $this->hasMany(PenangananKesanggupan::class);
    }
    public function histories()
    {
        return $this->hasMany(PenangananHistory::class);
    }
}
