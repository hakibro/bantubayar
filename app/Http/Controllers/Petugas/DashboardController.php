<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        $lembagaUser = auth()->user()->lembaga;

        $scope = Siswa::query();

        if (Auth::user()->hasRole('petugas')) {
            $scope = Penanganan::whereHas('petugas', function ($q) {
                $q->where('users.id', Auth::id());
            });
        } else {
            $scope = Penanganan::whereHas('siswa', function ($q) use ($lembagaUser) {
                $q->where('UnitFormal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatDiniyah', $lembagaUser);
            });
        }


        $summary = [
            'total' => (clone $scope)->count(),

            'menunggu_respon' => (clone $scope)
                ->where('status', 'menunggu_respon')
                ->count(),

            'menunggu_tindak_lanjut' => (clone $scope)
                ->where('status', 'menunggu_tindak_lanjut')
                ->count(),

            'selesai' => (clone $scope)
                ->where('status', 'selesai')
                ->count(),

            'tidak_ada_respon' => (clone $scope)
                ->where('hasil', 'tidak_ada_respon')
                ->count(),
        ];

        /**
         * =========================
         * 2. TUGAS AKTIF
         * =========================
         */
        $tugasAktif = (clone $scope)
            ->with('siswa')
            ->whereIn('status', [
                'menunggu_respon',
                'menunggu_tindak_lanjut'
            ])
            ->orderBy('created_at')
            ->get()
            ->map(function ($item) {
                $item->lama_menunggu = Carbon::parse($item->updated_at)->diffForHumans();
                return $item;
            });

        /**
         * =========================
         * 3. PENANGANAN TERLAMBAT
         * =========================
         */
        $penangananTerlambat = (clone $scope)
            ->with('siswa')
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('status', 'menunggu_respon')
                        ->where('updated_at', '<=', now()->subDays(2));
                })->orWhere(function ($sub) {
                    $sub->where('status', 'menunggu_tindak_lanjut')
                        ->where('updated_at', '<=', now()->subDays(3));
                });
            })
            ->orderBy('updated_at')
            ->get();

        /**
         * =========================
         * 4. PENANGANAN SELESAI + RATING
         * =========================
         */
        $penangananSelesai = (clone $scope)
            ->with('siswa')
            ->where('status', 'selesai')
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        /**
         * =========================
         * 5. STATISTIK RESPON WALI
         * =========================
         */
        $ratingQuery = (clone $scope)
            ->whereNotNull('rating');

        $statistikRespon = [
            'rata_rata' => round($ratingQuery->avg('rating'), 2),
            'total_dinilai' => $ratingQuery->count(),
            'responsif' => (clone $ratingQuery)->where('rating', '>=', 4)->count(),
            'kurang_responsif' => (clone $ratingQuery)->where('rating', '<=', 2)->count(),
        ];

        /**
         * =========================
         * 6. CATATAN RESPON WALI TERBARU
         * =========================
         */
        $catatanTerbaru = (clone $scope)
            ->with('siswa')
            ->whereNotNull('rating')
            ->whereNotNull('catatan')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('petugas.dashboard', compact(
            'summary',
            'tugasAktif',
            'penangananTerlambat',
            'penangananSelesai',
            'statistikRespon',
            'catatanTerbaru'
        ));
    }

}
