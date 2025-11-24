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
        'jenis',
        'catatan',
        'rating',
        'hasil',
        'tanggal_rekom',
        'status',
    ];

    protected $casts = [
        'rating' => 'array',
        'tanggal_rekom' => 'date',
    ];

    /**
     * Relasi ke model Siswa
     */
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    /**
     * Relasi ke model Petugas
     */
    public function petugas()
    {
        return $this->belongsTo(Users::class, 'id_petugas');
    }
}
