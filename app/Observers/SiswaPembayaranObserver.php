<?php

namespace App\Observers;

use App\Models\SiswaPembayaran;

class SiswaPembayaranObserver
{
    /**
     * Sebelum simpan: Hitung status lunas untuk baris periode ini dari kolom summary.
     */
    public function saving(SiswaPembayaran $pembayaran)
    {
        // Ambil dari kolom summary (array) yang berisi fully_paid periode ini
        $summary = $pembayaran->summary;
        $isLunas = $summary['fully_paid'] ?? false;

        $pembayaran->is_lunas = $isLunas;
    }

    /**
     * Setelah simpan: Hitung status lunas GLOBAL untuk model Siswa.
     */
    public function saved(SiswaPembayaran $pembayaran)
    {
        $siswa = $pembayaran->siswa;

        if ($siswa) {
            // Cek apakah ada satu saja periode milik siswa ini yang is_lunas-nya false
            $masihAdaTunggakan = SiswaPembayaran::where('siswa_id', $siswa->id)
                ->where('is_lunas', false)
                ->exists();

            // Update kolom is_lunas di tabel siswa
            $siswa->update([
                'is_lunas' => !$masihAdaTunggakan
            ]);
        }
    }
}