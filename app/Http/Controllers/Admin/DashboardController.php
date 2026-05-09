<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeVisit;
use App\Models\Penanganan;
use App\Models\Siswa;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard admin dengan statistik.
     */
    public function index()
    {
        $activeStatuses = ['menunggu_respon', 'menunggu_tindak_lanjut'];
        $thisMonth = fn($query) => $query
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year);

        $lunasQuery = Siswa::query()->lunas();
        $belumLunasQuery = Siswa::query()->belumLunas();

        $totalSiswa = Siswa::count();
        $siswaLunas = (clone $lunasQuery)->count();
        $siswaBelumLunas = (clone $belumLunasQuery)->count();

        $countPenangananSiswa = fn($siswaQuery, $statusQuery) => $statusQuery(
            Penanganan::whereIn('id_siswa', (clone $siswaQuery)->select('v_siswa.idperson'))
        )->count();

        $penangananPembayaran = [
            'lunas' => [
                'aktif' => $countPenangananSiswa($lunasQuery, fn($query) => $thisMonth($query)->whereIn('status', $activeStatuses)),
                'selesai' => $countPenangananSiswa($lunasQuery, fn($query) => $thisMonth($query)->where('status', 'selesai')),
            ],
            'belum_lunas' => [
                'aktif' => $countPenangananSiswa($belumLunasQuery, fn($query) => $thisMonth($query)->whereIn('status', $activeStatuses)),
                'selesai' => $countPenangananSiswa($belumLunasQuery, fn($query) => $thisMonth($query)->where('status', 'selesai')),
            ],
        ];

        $totalPetugas = User::role('petugas')->count();
        $totalBendahara = User::role('bendahara')->count();

        $penangananAktif = Penanganan::whereIn('status', $activeStatuses)->count();
        $penangananSelesaiBulanIni = $thisMonth(Penanganan::where('status', 'selesai'))->count();
        $homeVisitAktif = HomeVisit::where('status', '!=', 'selesai')->count();
        $homeVisitBulanIni = HomeVisit::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $persentaseLunas = round(($siswaLunas / max($totalSiswa, 1)) * 100, 1);
        $persentaseBelumLunas = round(($siswaBelumLunas / max($totalSiswa, 1)) * 100, 1);

        return view('admin.dashboard', compact(
            'totalSiswa',
            'siswaLunas',
            'siswaBelumLunas',
            'persentaseLunas',
            'persentaseBelumLunas',
            'totalPetugas',
            'totalBendahara',
            'penangananAktif',
            'penangananSelesaiBulanIni',
            'penangananPembayaran',
            'homeVisitBulanIni',
            'homeVisitAktif'
        ));
    }
}
