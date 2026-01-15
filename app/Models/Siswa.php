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
    ];

    /**
     * Scope pencarian sederhana berdasarkan nama atau idperson
     */
    // public function scopeSearch($query, $keyword)
    // {
    //     return $query->where('nama', 'like', "%{$keyword}%")
    //         ->orWhere('idperson', 'like', "%{$keyword}%");
    // }
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

    public function getTotalTunggakan(): int
    {
        return $this->hitungTotalDariKategori(
            $this->getKategoriBelumLunas() ?? []
        );
    }
}
