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
}
