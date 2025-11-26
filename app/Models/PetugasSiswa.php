<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetugasSiswa extends Model
{
    protected $table = 'petugas_siswa';

    protected $fillable = [
        'petugas_id',
        'siswa_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
