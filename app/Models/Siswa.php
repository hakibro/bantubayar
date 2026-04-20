<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\Pembayaran;

class Siswa extends Model
{
    use HasFactory, Pembayaran;

    protected $table = 'siswa';

    protected $fillable = [
        'idperson',
        'nama',
        'is_lunas',
        'gender',
        'lahirtempat',
        'lahirtanggal',
        'phone',
        'UnitFormal',
        'KelasFormal',
        'AsramaPondok',
        'KamarPondok',
        'TingkatDiniyah',
        'KelasDiniyah',
    ];

    protected $casts = [
        'lahirtanggal' => 'date',
        'is_lunas' => 'boolean',
    ];

    /**
     * Scope pencarian sederhana berdasarkan nama atau idperson
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nama', 'like', "%{$keyword}%")
                ->orWhere('idperson', 'like', "%{$keyword}%");
        });
    }

    public function petugas()
    {
        return $this->belongsToMany(User::class, 'petugas_siswa', 'siswa_id', 'petugas_id')
            ->withTimestamps();
    }

    public function homeVisits()
    {
        return $this->hasMany(HomeVisit::class);
    }
    public function homeVisitsActive()
    {
        return $this->homeVisits()->where('status', '!=', 'selesai')->latest()->first();
    }

    public function penanganan()
    {
        return $this->hasMany(Penanganan::class, 'id_siswa');
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
            ->orderByDesc('created_at')->get();
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

    public function saldo()
    {
        return $this->hasOne(SiswaSaldo::class);
    }

    public function getSaldoNominalAttribute()
    {
        return $this->saldo?->saldo ?? 0;
    }

    public function pembayaran()
    {
        return $this->hasMany(SiswaPembayaran::class, 'siswa_id');
    }


    /**
     * Scope untuk mencari berdasarkan status pembayaran global
     */
    public function scopeStatusPembayaran($query, $status)
    {
        if ($status === 'lunas') {
            return $query->where('is_lunas', true);
        } elseif ($status === 'belum_lunas') {
            return $query->where('is_lunas', false);
        }
        return $query;
    }

    /**
     * Mendapatkan class Tailwind berdasarkan kolom is_lunas dan sinkronisasi
     */
    public function getStatusPembayaranBadgeAttribute(): string
    {
        if (is_null($this->getKategoriBelumLunas())) {
            return 'border-yellow-400 text-yellow-600';
        }

        if ($this->is_lunas) {
            return 'border-green-400 text-green-600';
        }

        return 'border-red-400 text-red-600';
    }

    /**
     * Mendapatkan label teks berdasarkan kolom is_lunas
     */
    public function getStatusPembayaranLabelAttribute(): string
    {
        if (is_null($this->getKategoriBelumLunas())) {
            return 'Belum Sinkron';
        }

        return $this->is_lunas ? 'Lunas' : 'Belum Lunas';
    }

    /**
     * Mendapatkan daftar kategori yang belum lunas dari semua periode pembayaran siswa.
     *
     * @return array|null  Null jika belum sinkron, array kosong jika semua lunas.
     */
    public function getKategoriBelumLunas(): ?array
    {
        if (!$this->pembayaran()->exists()) {
            return null;
        }

        $belumLunas = [];

        foreach ($this->pembayaran as $pay) {
            $categories = $pay->data ?? [];

            foreach ($categories as $category) {
                if (($category['summary']['fully_paid'] ?? true) === false) {
                    $unpaidItems = array_filter(
                        $category['items'] ?? [],
                        fn($item) =>
                        ($item['payment_status'] ?? '') === 'unpaid'
                        || ($item['remaining_balance'] ?? 0) != 0
                    );

                    $belumLunas[] = [
                        'periode' => $pay->periode,
                        'category_name' => $category['category_name'],
                        'summary' => $category['summary'],
                        'items' => array_values($unpaidItems),
                    ];
                }
            }
        }

        return $belumLunas;
    }

    /**
     * Mendapatkan semua kategori (per periode) yang memiliki sisa tidak nol (tunggakan atau overpaid)
     * @return array
     */
    public function getKategoriSaldoTidakNol(): array
    {
        $result = [];
        foreach ($this->pembayaran as $pay) {
            $periode = $pay->periode;
            $categories = $pay->data ?? [];

            if (empty($categories)) {
                continue;
            }

            foreach ($categories as $category) {
                // Gunakan summary dari kategori jika ada
                $summary = $category['summary'] ?? [];
                if (!empty($summary)) {
                    $totalTagihan = $summary['total_billed'] ?? 0;
                    $totalDibayar = $summary['total_paid'] ?? 0;
                    $sisa = $summary['total_remaining'] ?? ($totalDibayar - $totalTagihan);
                } else {
                    // Hitung manual dari items
                    $totalTagihan = 0;
                    $totalDibayar = 0;
                    $items = $category['items'] ?? [];
                    foreach ($items as $item) {
                        $totalTagihan += $item['amount_paid'] ?? 0;
                        $totalDibayar += $item['amount_billed'] ?? 0;
                    }
                    $sisa = $totalDibayar - $totalTagihan;
                }

                if ($sisa != 0) {
                    $result[] = [
                        'periode' => $periode,
                        'category_name' => $category['category_name'] ?? 'Unknown',
                        'summary' => [
                            'total_billed' => $totalTagihan,
                            'total_paid' => $totalDibayar,
                            'total_remaining' => $sisa,
                            'fully_paid' => ($sisa >= 0),
                        ],
                        'items' => array_map(function ($item) {
                            return [
                                'unit_name' => $item['unit_name'] ?? $item['unit_id'],
                                'amount_tagihan' => $item['amount_paid'] ?? 0,
                                'amount_dibayar' => $item['amount_billed'] ?? 0,
                                'remaining_balance' => ($item['amount_billed'] ?? 0) - ($item['amount_paid'] ?? 0),
                                'payment_status' => (($item['amount_billed'] ?? 0) - ($item['amount_paid'] ?? 0)) >= 0 ? 'paid' : 'unpaid',
                            ];
                        }, $category['items'] ?? []),
                        'kelas_info' => $pay->kelas_info ?? '-',
                        'type' => $sisa > 0 ? 'overpaid' : 'tunggakan',
                        'sisa' => $sisa,
                    ];
                }
            }
        }
        return $result;
    }

    /**
     * Hitung total tunggakan (sisa > 0)
     */
    /**
     * Hitung total tunggakan (total_remaining positif) dari semua periode
     */
    public function getTotalTunggakan(): int
    {
        return (int) $this->pembayaran->sum(function ($pay) {
            return $pay->summary['total_remaining'] ?? 0;
        });
    }

    /**
     * Hitung total overpaid (total_remaining negatif) dari semua periode
     */
    public function getTotalOverpaid(): int
    {
        return (int) $this->pembayaran->sum(function ($pay) {
            $remaining = $pay->summary['total_remaining'] ?? 0;
            // Hanya jumlahkan jika nilainya negatif (overpaid)
            return $remaining < 0 ? abs($remaining) : 0;
        });
    }

    public function getFormattedTotalTunggakanAttribute()
    {
        $total = $this->getTotalTunggakan();
        return 'Rp ' . number_format($total, 0, ',', '.');
    }
}