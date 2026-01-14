<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;


class Penanganan extends Model
{
    use HasFactory, SoftDeletes;

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
    public function histories()
    {
        return $this->hasMany(PenangananHistory::class);
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

}
