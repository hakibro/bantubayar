<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Penanganan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        $user = auth()->user();
        $range = $request->get('range', 'current_week');
        $scope = $user->penanganan()->with('siswa:id,idperson,nama');

        // Default Query untuk Statistik
        $statsQuery = (clone $scope);

        // Inisialisasi variabel untuk filter statistik rating (poin 4)
        $startDate = null;
        $endDate = now();

        if ($range === 'current_week') {
            $startDate = now()->startOfWeek();
            $statsQuery->where('updated_at', '>=', $startDate);
        } elseif ($range === 'last_week') {
            $startDate = now()->subWeek()->startOfWeek();
            $endDate = now()->subWeek()->endOfWeek();
            $statsQuery->whereBetween('updated_at', [$startDate, $endDate]);
        } elseif ($range === 'current_month') { // FILTER BARU
            $startDate = now()->startOfMonth();
            $statsQuery->where('updated_at', '>=', $startDate);
        } elseif ($range === 'older') {
            $statsQuery->where('updated_at', '<', now()->subWeek()->startOfWeek());
        } elseif ($range === 'all') { // FILTER BARU
            // Tidak menambahkan where clause agar menampilkan semua
            $startDate = null;
        }

        // 1. Summary
        $summaryData = $statsQuery->selectRaw("
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'menunggu_respon' THEN 1 END) as menunggu_respon,
        COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai,
        COUNT(CASE WHEN status = 'menunggu_tindak_lanjut' THEN 1 END) as menunggu_tindak_lanjut
    ")->first();

        $summary = [
            'total' => $summaryData->total ?? 0,
            'menunggu_respon' => $summaryData->menunggu_respon ?? 0,
            'menunggu_tindak_lanjut' => $summaryData->menunggu_tindak_lanjut ?? 0,
            'selesai' => $summaryData->selesai ?? 0,
        ];

        // 2. Daftar Kerja Prioritas
        $tugasAktif = (clone $scope)
            ->whereIn('status', ['menunggu_respon', 'menunggu_tindak_lanjut'])
            ->orderBy('updated_at', 'asc')
            ->get()
            ->map(function ($item) {
                $item->lama_menunggu = $item->updated_at->diffForHumans();
                return $item;
            });

        // 3. Penanganan Terlambat
        $penangananTerlambat = $tugasAktif->filter(function ($item) {
            if ($item->status == 'menunggu_respon')
                return $item->updated_at <= now()->subDays(2);
            if ($item->status == 'menunggu_tindak_lanjut')
                return $item->updated_at <= now()->subDays(3);
            return false;
        });

        // 4. Statistik & Catatan (Menyesuaikan rentang yang dipilih)
        $ratingQuery = (clone $scope)->whereNotNull('rating');

        if ($startDate) {
            $ratingQuery->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $statistikRespon = [
            'rata_rata' => round($ratingQuery->avg('rating'), 1) ?? 0,
            'total_dinilai' => $ratingQuery->count(),
            'responsif' => (clone $ratingQuery)->where('rating', '>=', 4)->count(),
        ];

        $catatanTerbaru = (clone $ratingQuery)->latest('updated_at')->take(3)->get();

        if ($request->ajax()) {
            return view('petugas.dashboard.partials.cards', compact('summary', 'range'));
        }

        return view('petugas.dashboard.index', compact(
            'summary',
            'tugasAktif',
            'penangananTerlambat',
            'statistikRespon',
            'catatanTerbaru',
            'range'
        ));
    }

}
