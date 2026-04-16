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
    public function pembayaranSummary()
    {
        return $this->hasOne(SiswaPembayaranSummary::class, 'siswa_id');
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
        // Jika data pembayaran belum ada sama sekali (Belum Sinkron)
        if (is_null($this->getKategoriBelumLunas())) {
            return 'border-yellow-400 text-yellow-600';
        }

        // Jika is_lunas true (Lunas)
        if ($this->is_lunas) {
            return 'border-green-400 text-green-600';
        }

        // Jika is_lunas false (Belum Lunas)
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


    public function getKategoriBelumLunas(): ?array
    {
        if (!$this->pembayaran()->exists()) {
            return null; // BELUM SYNC
        }

        $belumLunas = [];

        foreach ($this->pembayaran as $pay) {
            $data = $pay->data ?? [];

            foreach ($data['categories'] ?? [] as $category) {
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

        return $belumLunas; // bisa [] atau berisi
    }

    // public function getTotalTunggakan(): int
    // {
    //     return $this->hitungTotalDariKategori(
    //         $this->getKategoriBelumLunas() ?? []
    //     );
    // }

    public function getFormattedTotalTunggakanAttribute()
    {
        $total = $this->getTotalTunggakan(); // Memanggil fungsi yang sudah ada di trait Anda
        return 'Rp ' . number_format($total, 0, ',', '.');
    }





    // untuk mengambil data pembayaran sebelum ngalah mobile

    /**
     * Mendapatkan semua kategori (per periode) yang memiliki sisa tidak nol (tunggakan atau overpaid)
     * @return array
     */
    public function getKategoriSaldoTidakNol(): array
    {
        $result = [];
        foreach ($this->pembayaran as $pay) {
            $periode = $pay->periode;
            $data = $pay->data;
            if (empty($data['categories']))
                continue;

            foreach ($data['categories'] as $category) {
                // Hitung ulang summary berdasarkan item jika perlu
                $totalTagihan = 0;
                $totalDibayar = 0;
                $items = $category['items'] ?? [];
                foreach ($items as $item) {
                    $totalTagihan += $item['amount_paid'];   // amount_paid adalah tagihan
                    $totalDibayar += $item['amount_billed']; // amount_billed adalah dibayar
                }
                $sisa = $totalDibayar - $totalTagihan; // sisa = dibayar - tagihan (positif = overpaid, negatif = tunggakan)

                if ($sisa != 0) {
                    $result[] = [
                        'periode' => $periode,
                        'category_name' => $category['category_name'] ?? 'Unknown',
                        'summary' => [
                            'total_billed' => $totalTagihan,   // tagihan
                            'total_paid' => $totalDibayar,   // dibayar
                            'total_remaining' => $sisa,
                            'fully_paid' => ($sisa >= 0) ? true : false, // jika sisa >=0 berarti tidak kurang bayar
                        ],
                        'items' => array_map(function ($item) {
                            return [
                                'unit_name' => $item['unit_name'] ?? $item['unit_id'],
                                'amount_tagihan' => $item['amount_paid'],   // tagihan
                                'amount_dibayar' => $item['amount_billed'], // dibayar
                                'remaining_balance' => $item['amount_billed'] - $item['amount_paid'],
                                'payment_status' => ($item['amount_billed'] - $item['amount_paid']) >= 0 ? 'paid' : 'unpaid',
                            ];
                        }, $items),
                        'kelas_info' => $data['kelas_info'] ?? $pay->kelas_info ?? '-',
                        'type' => $sisa > 0 ? 'overpaid' : 'tunggakan', // karena sisa = dibayar - tagihan
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
    public function getTotalTunggakan(): int
    {
        $total = 0;
        foreach ($this->getKategoriSaldoTidakNol() as $item) {
            if ($item['type'] === 'tunggakan') {
                $total += abs($item['sisa']); // ambil nilai absolut tunggakan
            }
        }
        return $total;
    }

    public function getTotalOverpaid(): int
    {
        $total = 0;
        foreach ($this->getKategoriSaldoTidakNol() as $item) {
            if ($item['type'] === 'overpaid') {
                $total += $item['sisa']; // sudah positif
            }
        }
        return $total;
    }
}