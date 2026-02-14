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
        $range = $request->get('range', 'current_week'); // Default minggu ini
        $scope = $user->penanganan()->with('siswa:id,idperson,nama');

        // Tentukan rentang tanggal
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        // Default Query untuk Statistik
        $statsQuery = (clone $scope);

        if ($range === 'current_week') {
            // Senin ini jam 00:00:00 s/d sekarang
            $statsQuery->where('updated_at', '>=', now()->startOfWeek());
        } elseif ($range === 'last_week') {
            // Senin lalu s/d Minggu malam lalu
            $statsQuery->whereBetween('updated_at', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ]);
        } elseif ($range === 'older') {
            // Semua sebelum Senin minggu lalu jam 00:00:00
            $statsQuery->where('updated_at', '<', now()->subWeek()->startOfWeek());
        }

        // 1. Summary (Berdasarkan filter waktu)
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

        // 2. Daftar Kerja Prioritas (Ini biasanya tidak difilter tanggal agar tugas lama tidak hilang)
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

        // 4. Statistik & Catatan (Berdasarkan rentang waktu)
        $ratingQuery = (clone $scope)->whereBetween('updated_at', [$startDate, $endDate])->whereNotNull('rating');

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
