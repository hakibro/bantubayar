<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

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
    public function scopeSearch($query, $keyword)
    {
        return $query->where('nama', 'like', "%{$keyword}%")
            ->orWhere('idperson', 'like', "%{$keyword}%");
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

    public function pembayaran()
    {
        return $this->hasMany(SiswaPembayaran::class, 'siswa_id');
    }

    public function getKategoriBelumLunas()
    {
        $belumLunas = [];

        foreach ($this->pembayaran as $pay) {

            $data = $pay->data ?? [];

            foreach ($data['categories'] ?? [] as $category) {

                if (($category['summary']['fully_paid'] ?? true) === false) {

                    $unpaidItems = array_filter($category['items'] ?? [], function ($item) {
                        return ($item['payment_status'] ?? '') === 'unpaid'
                            || ($item['remaining_balance'] ?? 0) != 0;
                    });

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






}
