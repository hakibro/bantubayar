<?php

namespace App\Models;

use App\Services\PembayaranService;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'v_siswa';
    protected $primaryKey = 'idperson';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $casts = [
        'saldo' => 'integer',
    ];

    // Alias $siswa->id → idperson agar Blade view lama tidak langsung error
    public function getIdAttribute(): int
    {
        return $this->idperson;
    }

    public function getSaldoNominalAttribute(): int
    {
        return $this->saldo ?? 0;
    }

    public function totalTunggakan(): int
    {
        return app(PembayaranService::class)->getTotalBelumLunas((string) $this->idperson);
    }

    public function getTotalTunggakanAttribute(): int
    {
        if (array_key_exists('total_tunggakan', $this->attributes)) {
            return (int) ($this->attributes['total_tunggakan'] ?? 0);
        }

        return $this->totalTunggakan();
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('v_siswa.nama', 'like', "%{$keyword}%")
                ->orWhere('v_siswa.idperson', 'like', "%{$keyword}%");
        });
    }

    // Relasi ke Phone (Hanya mengambil jika dipanggil)
    public function phone()
    {
        return $this->hasOne(SiswaPhone::class, 'idperson', 'idperson');
    }

    // Relasi ke Status Lunas (Hanya mengambil jika dipanggil)
    public function statusLunas()
    {
        return $this->hasOne(StatusLunasSiswa::class, 'idperson', 'idperson');
    }

    public function scopeStatusPembayaran($query, $status)
    {
        if ($status === 'lunas') {
            return $query->lunas();
        } elseif ($status === 'belum_lunas') {
            return $query->belumLunas();
        }
        return $query;
    }

    public function scopeLunas($query)
    {
        return $query->whereHas('statusLunas', fn($q) => $q->where('is_lunas', 1));
    }

    public function scopeBelumLunas($query)
    {
        return $query->whereHas('statusLunas', fn($q) => $q->where('is_lunas', 0));
    }

    public function petugas()
    {
        return $this->belongsToMany(User::class, 'petugas_siswa', 'siswa_id', 'petugas_id')
            ->withTimestamps();
    }

    public function homeVisits()
    {
        return $this->hasMany(HomeVisit::class, 'siswa_id', 'idperson');
    }

    public function homeVisitsActive()
    {
        return $this->homeVisits()->where('status', '!=', 'selesai')->latest()->first();
    }

    public function penanganan()
    {
        return $this->hasMany(Penanganan::class, 'id_siswa', 'idperson');
    }

    public function penangananAktif()
    {
        return $this->penanganan()
            ->where('status', '!=', 'selesai')
            ->latest()
            ->first();
    }

    public function penangananSelesai()
    {
        return $this->penanganan()
            ->where('status', 'selesai')
            ->orderByDesc('created_at')
            ->get();
    }

    public function penangananLunas()
    {
        return $this->penanganan()
            ->where(['status' => 'selesai', 'hasil' => 'lunas'])
            ->latest()
            ->first();
    }

    public function sedangDitangani(): bool
    {
        return $this->penanganan()
            ->where('status', '!=', 'selesai')
            ->exists();
    }

    public function petugasPenangananAktif(): ?string
    {
        return optional($this->penangananAktif()?->petugas)->name;
    }

    public function getStatusPembayaranBadgeAttribute(): string
    {
        return $this->totalTunggakan > 0
            ? 'border-red-400 text-red-600'
            : 'border-green-400 text-green-600';
    }

    public function getStatusPembayaranLabelAttribute(): string
    {
        return $this->totalTunggakan > 0 ? 'Belum Lunas' : 'Lunas';
    }
}
