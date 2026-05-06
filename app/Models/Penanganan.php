<?php

namespace App\Models;

use App\Services\PembayaranService;
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
        'rating'           => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa', 'idperson');
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

    public function getTanggalKesanggupanFormattedAttribute(): string
    {
        return $this->kesanggupanTerakhir
            ? \Carbon\Carbon::parse($this->kesanggupanTerakhir->tanggal)->format('Y-m-d')
            : '';
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
     * Ambil penanganan aktif atau buat baru.
     * jenis_pembayaran diisi dari query langsung ke daruttaqwa_trans.
     */
    public static function getOrCreateForSiswa(Siswa $siswa): self
    {
        $penanganan = self::where('id_siswa', $siswa->idperson)
            ->latest()
            ->first();

        if (!$penanganan || $penanganan->status === 'selesai') {
            $service    = app(PembayaranService::class);
            $belumLunas = $service->getDetailBelumLunas((string) $siswa->idperson);

            $penanganan = self::create([
                'id_siswa'         => $siswa->idperson,
                'id_petugas'       => Auth::id(),
                'jenis_pembayaran' => array_map(fn($row) => (array) $row, $belumLunas),
                'saldo'            => $siswa->saldo ?? 0,
                'status'           => 'menunggu_respon',
            ]);
        }

        return $penanganan;
    }

    public function addHistory(string $jenis, ?string $catatan = null): void
    {
        $this->histories()->create([
            'jenis_penanganan' => $jenis,
            'catatan'          => $catatan,
        ]);
    }

    /**
     * Total kurang bayar dari snapshot jenis_pembayaran.
     * Mendukung format baru (field 'selisih') dan format lama (nested 'items'→'remaining_balance').
     */
    public function getTotalTunggakan(): int
    {
        $items = $this->jenis_pembayaran ?? [];

        if (empty($items)) return 0;

        // Format baru dari PembayaranService: flat array dengan field 'selisih'
        if (isset($items[0]['selisih'])) {
            return (int) collect($items)->sum('selisih');
        }

        // Format lama dari API: nested items → remaining_balance
        return (int) collect($items)
            ->flatMap(fn($k) => $k['items'] ?? [])
            ->sum(fn($i) => (int) ($i['remaining_balance'] ?? 0));
    }
}
