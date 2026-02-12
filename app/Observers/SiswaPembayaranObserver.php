<?php

namespace App\Observers;

use App\Models\SiswaPembayaran;

class SiswaPembayaranObserver
{
    /**
     * Sebelum simpan: Hitung status lunas untuk baris periode ini saja.
     */
    public function saving(SiswaPembayaran $pembayaran)
    {
        $data = $pembayaran->data;
        $isLunas = true;

        if (isset($data['categories']) && is_array($data['categories'])) {
            foreach ($data['categories'] as $category) {
                if (($category['summary']['fully_paid'] ?? true) === false) {
                    $isLunas = false;
                    break;
                }
            }
        } else {
            $isLunas = false;
        }

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
            // Jika ada yang false (0), berarti status global siswa adalah belum lunas.
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