<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiswaPembayaranSummary extends Model
{
    use HasFactory;

    /**
     * Mendefinisikan nama tabel secara eksplisit 
     * (Opsional jika nama tabel sudah sesuai konvensi plural)
     */
    protected $table = 'siswa_pembayaran_summary';

    /**
     * Field yang dapat diisi melalui mass assignment.
     */
    protected $fillable = [
        'siswa_id',
        'data',
        'is_lunas',
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     * * Sangat penting: 'data' di-cast ke 'array' agar Anda bisa langsung 
     * memasukkan/mengambil array PHP tanpa perlu manual json_encode/decode.
     */
    protected $casts = [
        'data' => 'array',
        'is_lunas' => 'boolean',
    ];

    /**
     * Relasi ke model Siswa.
     * Mengasumsikan Anda memiliki model bernama Siswa.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Scope untuk memudahkan filter data yang sudah lunas.
     */
    public function scopeLunas($query)
    {
        return $query->where('is_lunas', true);
    }

    /**
     * Scope untuk memudahkan filter data yang belum lunas.
     */
    public function scopeBelumLunas($query)
    {
        return $query->where('is_lunas', false);
    }
}