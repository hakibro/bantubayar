<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Penanganan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPetugasController extends Controller
{
    /**
     * Tampilkan halaman laporan aktivitas petugas/bendahara.
     */
    public function index(Request $request)
    {
        // Default tanggal: awal bulan sampai hari ini
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $petugasId = $request->input('petugas_id');

        // Ambil semua petugas & bendahara untuk dropdown
        $petugasList = User::role(['petugas', 'bendahara'])->orderBy('name')->get();

        // Query dasar penanganan dengan relasi
        $query = Penanganan::with(['petugas', 'siswa'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        // Filter berdasarkan petugas jika dipilih
        if ($petugasId) {
            $query->where('id_petugas', $petugasId);
        }

        // Ambil data penanganan
        $penanganan = $query->orderBy('created_at', 'desc')->get();

        // Statistik ringkasan
        $totalPenanganan = $penanganan->count();
        $selesai = $penanganan->where('status', 'selesai')->count();
        $menungguRespon = $penanganan->where('status', 'menunggu_respon')->count();
        $menungguTindakLanjut = $penanganan->where('status', 'menunggu_tindak_lanjut')->count();

        // Rata-rata rating (hanya yang selesai dan punya rating)
        $ratingAvg = $penanganan->whereNotNull('rating')->avg('rating');
        $ratingAvg = $ratingAvg ? round($ratingAvg, 2) : 0;

        // Breakdown hasil
        $hasilBreakdown = $penanganan->where('status', 'selesai')
            ->groupBy('hasil')
            ->map(function ($group) {
                return $group->count();
            });

        return view('admin.laporan.petugas', compact(
            'petugasList',
            'startDate',
            'endDate',
            'petugasId',
            'penanganan',
            'totalPenanganan',
            'selesai',
            'menungguRespon',
            'menungguTindakLanjut',
            'ratingAvg',
            'hasilBreakdown'
        ));
    }

    /**
     * Export laporan ke PDF (opsional)
     */
    public function exportPdf(Request $request)
    {
        // Nanti bisa ditambahkan
    }
}