<?php

namespace App\Http\Controllers;

use App\Models\HomeVisit;
use App\Models\Penanganan;
use Illuminate\Http\Request;
use App\Traits\ImageCompressor;
use Illuminate\Support\Facades\Log;

class VisitController extends Controller
{
    use ImageCompressor;

    /**
     * Halaman gabungan untuk petugas home-visit (tanpa login):
     * menampilkan detail tugas sekaligus form laporan.
     */
    public function form($token)
    {
        $homeVisit = HomeVisit::with('siswa')->where('token', $token)->firstOrFail();

        // Link hanya boleh diakses setelah disetujui admin.
        if ($homeVisit->status === 'pending') {
            return view('visit.belum-disetujui', compact('homeVisit'));
        }

        if (in_array($homeVisit->status, ['ditolak', 'batal'], true)) {
            return view('visit.tidak-berlaku', compact('homeVisit'));
        }

        // Sudah dilaporkan → halaman info.
        if ($homeVisit->status === 'selesai') {
            return view('visit.sudah-dilaporkan', compact('homeVisit'));
        }

        // Tandai sedang dilaksanakan saat link pertama dibuka.
        if ($homeVisit->status === 'disetujui') {
            $homeVisit->update(['status' => 'dilaksanakan']);
        }

        return view('visit.form', compact('homeVisit'));
    }

    /**
     * Proses submit laporan home visit.
     */
    public function submit(Request $request, $token)
    {
        $homeVisit = HomeVisit::with('siswa')->where('token', $token)->firstOrFail();

        if (!$homeVisit->bolehDilaporkan()) {
            return redirect()->route('visit.form', $token)
                ->with('error', 'Laporan tidak dapat dikirim untuk home visit ini.');
        }

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
            if (!is_array($files)) {
                $files = [$files];
            }
            $fotoPaths = $this->compressMultipleImages($files);
            Log::info('Hasil kompresi foto home visit:', $fotoPaths);
        }

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

        // Catat ke riwayat penanganan bila terkait.
        $this->catatRiwayatPenanganan($homeVisit);

        return redirect()->route('visit.thankyou')->with('success', 'Laporan berhasil dikirim.');
    }

    /**
     * Tulis aksi home_visit ke penanganan_history agar masuk ke alur penanganan.
     */
    protected function catatRiwayatPenanganan(HomeVisit $homeVisit): void
    {
        $penanganan = $homeVisit->penanganan
            ?? Penanganan::where('id_siswa', $homeVisit->siswa_id)->latest()->first();

        if (!$penanganan) {
            return;
        }

        $laporan = $homeVisit->laporan ?? [];
        $hasil = $laporan['hasil_lainnya'] ?? ($laporan['hasil'] ?? '-');

        $penanganan->addHistory(
            'home_visit',
            'Home visit oleh ' . ($homeVisit->petugas_nama ?: 'petugas') . ' — hasil: ' . $hasil
                . (($laporan['catatan'] ?? null) ? '. Catatan: ' . $laporan['catatan'] : '')
        );

        // Bila home visit berhasil, tandai penanganan butuh tindak lanjut pembayaran.
        if (($laporan['hasil'] ?? null) === 'berhasil' && $penanganan->status !== 'selesai') {
            $penanganan->update(['status' => 'menunggu_tindak_lanjut']);
        }
    }

    /**
     * Halaman terima kasih setelah submit.
     */
    public function thankyou()
    {
        return view('visit.thankyou');
    }
}
