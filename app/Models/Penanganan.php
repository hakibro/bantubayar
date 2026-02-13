<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\Pembayaran;



class Penanganan extends Model
{
    use HasFactory, SoftDeletes, Pembayaran;

    protected $table = 'penanganan';

    protected $fillable = [
        'id_siswa',
        'id_petugas',
        'jenis_pembayaran',
        'saldo',
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
    public function kesanggupanTerakhir()
    {
        return $this->hasOne(PenangananKesanggupan::class)->latestOfMany();
    }

    public function getTanggalKesanggupanFormattedAttribute()
    {
        return $this->kesanggupanTerakhir ? \Carbon\Carbon::parse($this->kesanggupanTerakhir->tanggal)->format('Y-m-d') : '';
    }

    public function histories()
    {
        return $this->hasMany(PenangananHistory::class);
    }
    public function lastHistory()
    {
        return $this->hasOne(PenangananHistory::class)->latest();
    }

    /**
     * Ambil penanganan aktif atau buat baru
     */
    public static function getOrCreateForSiswa(Siswa $siswa): self
    {
        $penanganan = self::where('id_siswa', $siswa->id)
            ->latest()
            ->first();

        if (!$penanganan || $penanganan->status === 'selesai') {
            $penanganan = self::create([
                'id_siswa' => $siswa->id,
                'id_petugas' => Auth::id(),
                'jenis_pembayaran' => $siswa->getKategoriBelumLunas(),
                'saldo' => $siswa->saldo->saldo ?? 0,
                'status' => 'menunggu_respon',
            ]);
        }

        return $penanganan;
    }

    /**
     * Tambah history penanganan
     */
    public function addHistory(string $jenis, ?string $catatan = null): void
    {
        $this->histories()->create([
            'jenis_penanganan' => $jenis,
            'catatan' => $catatan,
        ]);
    }

    public function getTotalTunggakan(): int
    {
        return $this->hitungTotalDariKategori(
            $this->jenis_pembayaran ?? []
        );
    }


    // ambil kategori pembayaran yang sudah lunas
    public function getKategoriYangSudahLunas(Siswa $siswa): array
    {
        $belumLunasKeys = collect($siswa->getKategoriBelumLunas() ?? [])
            ->map(fn($k) => ($k['category_name'] ?? '') . '|' . ($k['periode'] ?? ''));

        return collect($this->jenis_pembayaran ?? [])
            ->reject(
                fn($k) =>
                $belumLunasKeys->contains(
                    ($k['category_name'] ?? '') . '|' . ($k['periode'] ?? '')
                )
            )
            ->values()
            ->all();
    }



}
