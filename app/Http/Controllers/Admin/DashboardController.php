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
        // Statistik Siswa
        $totalSiswa = Siswa::count();
        $siswaLunas = Siswa::where('is_lunas', true)->count();
        $siswaBelumLunas = Siswa::where('is_lunas', false)->count();
        $siswaBelumSync = Siswa::whereDoesntHave('pembayaran')->count();

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
            'siswaBelumSync',
            'totalPetugas',
            'totalBendahara',
            'penangananAktif',
            'homeVisitAktif'
        ));
    }
}