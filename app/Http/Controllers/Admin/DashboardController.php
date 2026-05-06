<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Penanganan;
use App\Models\HomeVisit;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman dashboard admin dengan statistik.
     */
    public function index()
    {


        $baseSiswaQuery = Siswa::query();
        // Total semua siswa
        $totalSiswa = (clone $baseSiswaQuery)->count();

        // Siswa LUNAS — join langsung, tidak pluck semua ID ke PHP
        $lunasQuery = (clone $baseSiswaQuery)
            ->join('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_siswa.idperson')
            ->where('sl.is_lunas', 1);
        $siswaLunas = (clone $lunasQuery)->count();

        // Penanganan dari siswa lunas (pakai subquery, bukan whereIn array)
        $penangananLunasAktif = Penanganan::whereIn('id_siswa', (clone $lunasQuery)->select('v_siswa.idperson'))
            ->whereIn('status', ['menunggu_respon', 'menunggu_tindak_lanjut'])
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $penangananLunasSelesai = Penanganan::whereIn('id_siswa', (clone $lunasQuery)->select('v_siswa.idperson'))
            ->where('status', 'selesai')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Siswa BELUM LUNAS
        $belumLunasQuery = (clone $baseSiswaQuery)
            ->join('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_siswa.idperson')
            ->where('sl.is_lunas', 0);
        $siswaBelumLunas = (clone $belumLunasQuery)->count();

        // Penanganan dari siswa belum lunas (pakai subquery)
        $penangananBelumLunasAktif = Penanganan::whereIn('id_siswa', (clone $belumLunasQuery)->select('v_siswa.idperson'))
            ->whereIn('status', ['menunggu_respon', 'menunggu_tindak_lanjut'])
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();
        $penangananBelumLunasSelesai = Penanganan::whereIn('id_siswa', (clone $belumLunasQuery)->select('v_siswa.idperson'))
            ->where('status', 'selesai')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Statistik Pengguna
        $totalPetugas = User::role('petugas')->count();
        $totalBendahara = User::role('bendahara')->count();

        // Statistik Penanganan & Home Visit
        $penangananAktif = Penanganan::whereIn('status', ['menunggu_respon', 'menunggu_tindak_lanjut'])->count();
        $homeVisitAktif = HomeVisit::where('status', '!=', 'selesai')->count();

        return view('admin.dashboard', compact(
            'totalSiswa',
            'siswaLunas',
            'siswaBelumLunas',
            'totalPetugas',
            'totalBendahara',
            'penangananAktif',
            'homeVisitAktif'
        ));
    }
}