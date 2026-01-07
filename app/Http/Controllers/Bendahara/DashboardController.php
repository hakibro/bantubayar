<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // public function index()
    // {
    //     $petugasId = Auth::id();

    //     /**
    //      * =========================
    //      * 1. SUMMARY CARDS
    //      * =========================
    //      */
    //     $summary = [
    //         'total' => Penanganan::where('id_petugas', $petugasId)->count(),

    //         'menunggu_respon' => Penanganan::where('id_petugas', $petugasId)
    //             ->where('status', 'menunggu_respon')
    //             ->count(),

    //         'menunggu_tindak_lanjut' => Penanganan::where('id_petugas', $petugasId)
    //             ->where('status', 'menunggu_tindak_lanjut')
    //             ->count(),

    //         'selesai' => Penanganan::where('id_petugas', $petugasId)
    //             ->where('status', 'selesai')
    //             ->count(),

    //         'tidak_ada_respon' => Penanganan::where('id_petugas', $petugasId)
    //             ->where('hasil', 'tidak_ada_respon')
    //             ->count(),
    //     ];

    //     /**
    //      * =========================
    //      * 2. TUGAS AKTIF
    //      * =========================
    //      */
    //     $tugasAktif = Penanganan::with('siswa')
    //         ->where('id_petugas', $petugasId)
    //         ->whereIn('status', [
    //             'menunggu_respon',
    //             'menunggu_tindak_lanjut'
    //         ])
    //         ->orderBy('created_at')
    //         ->get()
    //         ->map(function ($item) {
    //             $item->lama_menunggu = Carbon::parse($item->updated_at)->diffForHumans();
    //             return $item;
    //         });

    //     /**
    //      * =========================
    //      * 3. PENANGANAN TERLAMBAT
    //      * =========================
    //      */
    //     $penangananTerlambat = Penanganan::with('siswa')
    //         ->where('id_petugas', $petugasId)
    //         ->where(function ($q) {
    //             $q->where(function ($sub) {
    //                 $sub->where('status', 'menunggu_respon')
    //                     ->where('updated_at', '<=', now()->subDays(2));
    //             })->orWhere(function ($sub) {
    //                 $sub->where('status', 'menunggu_tindak_lanjut')
    //                     ->where('updated_at', '<=', now()->subDays(3));
    //             });
    //         })
    //         ->orderBy('updated_at')
    //         ->get();

    //     /**
    //      * =========================
    //      * 4. PENANGANAN SELESAI + RATING
    //      * =========================
    //      */
    //     $penangananSelesai = Penanganan::with('siswa')
    //         ->where('id_petugas', $petugasId)
    //         ->where('status', 'selesai')
    //         ->orderByDesc('updated_at')
    //         ->limit(10)
    //         ->get();

    //     /**
    //      * =========================
    //      * 5. STATISTIK RESPON WALI
    //      * =========================
    //      */
    //     $ratingQuery = Penanganan::where('id_petugas', $petugasId)
    //         ->whereNotNull('rating');

    //     $statistikRespon = [
    //         'rata_rata' => round($ratingQuery->avg('rating'), 2),
    //         'total_dinilai' => $ratingQuery->count(),
    //         'responsif' => (clone $ratingQuery)->where('rating', '>=', 4)->count(),
    //         'kurang_responsif' => (clone $ratingQuery)->where('rating', '<=', 2)->count(),
    //     ];

    //     /**
    //      * =========================
    //      * 6. CATATAN RESPON WALI TERBARU
    //      * =========================
    //      */
    //     $catatanTerbaru = Penanganan::with('siswa')
    //         ->where('id_petugas', $petugasId)
    //         ->whereNotNull('rating')
    //         ->whereNotNull('catatan')
    //         ->orderByDesc('updated_at')
    //         ->limit(5)
    //         ->get();

    //     return view('bendahara.dashboard', compact(
    //         'summary',
    //         'tugasAktif',
    //         'penangananTerlambat',
    //         'penangananSelesai',
    //         'statistikRespon',
    //         'catatanTerbaru'
    //     ));
    // }
    public function index()
    {
        $lembagaUser = auth()->user()->lembaga;

        /**
         * =================================================
         * BASE QUERY: Penanganan sesuai lembaga petugas
         * =================================================
         */
        $basePenanganan = Penanganan::whereHas('siswa', function ($q) use ($lembagaUser) {
            $q->where('UnitFormal', $lembagaUser)
                ->orWhere('AsramaPondok', $lembagaUser)
                ->orWhere('TingkatDiniyah', $lembagaUser);
        });

        /**
         * =========================
         * 1. SUMMARY CARDS
         * =========================
         */
        $summary = [
            'total' => (clone $basePenanganan)->count(),

            'menunggu_respon' => (clone $basePenanganan)
                ->where('status', 'menunggu_respon')
                ->count(),

            'menunggu_tindak_lanjut' => (clone $basePenanganan)
                ->where('status', 'menunggu_tindak_lanjut')
                ->count(),

            'selesai' => (clone $basePenanganan)
                ->where('status', 'selesai')
                ->count(),

            'tidak_ada_respon' => (clone $basePenanganan)
                ->where('hasil', 'tidak_ada_respon')
                ->count(),
        ];

        /**
         * =========================
         * 2. TUGAS AKTIF
         * =========================
         */
        $tugasAktif = (clone $basePenanganan)
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
        $penangananTerlambat = (clone $basePenanganan)
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
        $penangananSelesai = (clone $basePenanganan)
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
        $ratingQuery = (clone $basePenanganan)
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
        $catatanTerbaru = (clone $basePenanganan)
            ->with('siswa')
            ->whereNotNull('rating')
            ->whereNotNull('catatan')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('bendahara.dashboard', compact(
            'summary',
            'tugasAktif',
            'penangananTerlambat',
            'penangananSelesai',
            'statistikRespon',
            'catatanTerbaru'
        ));
    }

}
