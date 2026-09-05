<?php

namespace App\Models;

use App\Services\PembayaranService;
use Illuminate\Database\Eloquent\Model;

class Alumni extends Model
{
    protected $table = 'v_alumni';

    protected $primaryKey = 'idperson';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $casts = [
        'saldo' => 'integer',
        'tahun_lulus' => 'integer',
    ];

    // Alias agar konsisten dengan Siswa (beberapa view/partial memakai $alumni->id)
    public function getIdAttribute(): int
    {
        return (int) $this->idperson;
    }

    public function getSaldoNominalAttribute(): int
    {
        return (int) ($this->saldo ?? 0);
    }

    // Total tunggakan dihitung langsung dari ips_siswa (live), bukan cache.
    public function totalTunggakan(): int
    {
        return app(PembayaranService::class)->getTotalBelumLunas((string) $this->idperson);
    }

    // Di daftar, kolom total_tunggakan berasal dari join cache v_status_lunas_siswa.
    // Jika tidak tersedia (mis. detail), hitung live dari ips_siswa.
    public function getTotalTunggakanAttribute(): int
    {
        if (array_key_exists('total_tunggakan', $this->attributes)) {
            return (int) ($this->attributes['total_tunggakan'] ?? 0);
        }

        return $this->totalTunggakan();
    }

    // Kompatibel dengan pola $siswa->is_lunas yang dipakai di view penanganan.
    public function getIsLunasAttribute(): bool
    {
        if (array_key_exists('is_lunas', $this->attributes)) {
            return (int) $this->attributes['is_lunas'] === 1;
        }

        return $this->totalTunggakan() <= 0;
    }

    public function phone()
    {
        return $this->hasOne(SiswaPhone::class, 'idperson', 'idperson');
    }

    public function statusLunas()
    {
        return $this->hasOne(StatusLunasSiswa::class, 'idperson', 'idperson');
    }

    public function penanganan()
    {
        return $this->hasMany(Penanganan::class, 'id_siswa', 'idperson');
    }

    public function latestPenanganan()
    {
        return $this->hasOne(Penanganan::class, 'id_siswa', 'idperson')->latestOfMany();
    }

    public function penangananSelesai()
    {
        return $this->penanganan()
            ->where('status', 'selesai')
            ->orderByDesc('created_at')
            ->get();
    }

    public function penangananAktif()
    {
        return $this->penanganan()
            ->where('status', '!=', 'selesai')
            ->latest()
            ->first();
    }

    public function petugasPenangananAktif(): ?string
    {
        return optional($this->penangananAktif()?->petugas)->name;
    }
}
