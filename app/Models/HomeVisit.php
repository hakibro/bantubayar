<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeVisit extends Model
{
    protected $table = 'home_visits';

    protected $fillable = [
        'siswa_id',
        'admin_id',
        'penanganan_id',
        'diajukan_oleh',
        'disetujui_oleh',
        'disetujui_at',
        'petugas_nama',
        'petugas_hp',
        'token',
        'tanggal_visit',
        'alasan_pengajuan',
        'status',
        'laporan',
    ];

    protected $casts = [
        'laporan' => 'array',
        'tanggal_visit' => 'date',
        'disetujui_at' => 'datetime',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'idperson');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function penanganan(): BelongsTo
    {
        return $this->belongsTo(Penanganan::class, 'penanganan_id');
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Apakah link laporan sudah boleh diakses/diisi oleh petugas home-visit.
     * Hanya home visit yang sudah disetujui admin dan belum selesai.
     */
    public function bolehDilaporkan(): bool
    {
        return in_array($this->status, ['disetujui', 'dijadwalkan', 'dilaksanakan'], true);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', 'selesai');
    }
}