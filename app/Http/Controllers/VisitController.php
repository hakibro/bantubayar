<?php

namespace App\Http\Controllers;

use App\Models\HomeVisit;
use Illuminate\Http\Request;
use App\Traits\ImageCompressor;
use Illuminate\Support\Facades\Storage;

class VisitController extends Controller
{
    use ImageCompressor;
    /**
     * Tampilkan form laporan home visit
     */
    public function form($token)
    {
        $homeVisit = HomeVisit::with('siswa')->where('token', $token)->firstOrFail();

        // Jika sudah dilaporkan, arahkan ke halaman info
        if ($homeVisit->status === 'selesai') {
            return view('visit.sudah-dilaporkan', compact('homeVisit'));
        }

        return view('visit.form', compact('homeVisit'));
    }

    /**
     * Proses submit laporan home visit
     */
    public function submit(Request $request, $token)
    {
        $homeVisit = HomeVisit::with('siswa')->where('token', $token)->firstOrFail();

        $request->validate([
            'foto.*' => 'nullable|image',
            'lokasi' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
            'hasil' => 'required|in:berhasil,gagal,tidak_ditemukan,menolak,lainnya',
            'hasil_lainnya' => 'required_if:hasil,lainnya|nullable|string|max:255',
        ]);

        // Upload & kompres foto
        $fotoPaths = [];
        if ($request->hasFile('foto')) {
            $files = $request->file('foto');
            // Pastikan dalam bentuk array
            if (!is_array($files)) {
                $files = [$files];
            }
            // Gunakan trait untuk kompresi
            $fotoPaths = $this->compressMultipleImages($files);
            \Log::info('Hasil kompresi foto:', $fotoPaths);
        }

        // Data laporan
        $laporan = [
            'foto' => $fotoPaths,
            'lokasi' => $request->lokasi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'catatan' => $request->catatan,
            'hasil' => $request->hasil,
            'hasil_lainnya' => $request->hasil_lainnya,
            'waktu_lapor' => now()->toDateTimeString(),
        ];

        $homeVisit->update([
            'laporan' => $laporan,
            'status' => 'selesai',
        ]);

        return redirect()->route('visit.thankyou')->with('success', 'Laporan berhasil dikirim.');
    }

    /**
     * Halaman terima kasih setelah submit
     */
    public function thankyou()
    {
        return view('visit.thankyou');
    }
}