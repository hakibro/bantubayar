<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeVisit extends Model
{
    protected $table = 'home_visits';

    protected $fillable = [
        'siswa_id',
        'admin_id',
        'petugas_nama',
        'petugas_hp',
        'token',
        'tanggal_visit',
        'status',
        'laporan',
    ];

    protected $casts = [
        'laporan' => 'array',
        'tanggal_visit' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}