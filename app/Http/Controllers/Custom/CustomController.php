<?php

namespace App\Http\Controllers\Custom;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\SiswaPembayaran;
use App\Exports\SiswaBelumLunasExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');
        $unitFormal = $request->get('unit_formal');
        $asramaPondok = $request->get('asrama_pondok');
        $tingkatDiniyah = $request->get('tingkat_diniyah');

        // Query semua siswa yang memiliki pembayaran periode < '20242025'
        $query = Siswa::whereHas('pembayaran', function ($q) {
            $q->where('periode', '<', '20242025');
        })->with([
                    'pembayaran' => function ($q) {
                        $q->where('periode', '<', '20242025');
                    }
                ]);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('idperson', 'like', "%{$keyword}%");
            });
        }
        if ($unitFormal)
            $query->where('UnitFormal', $unitFormal);
        if ($asramaPondok)
            $query->where('AsramaPondok', $asramaPondok);
        if ($tingkatDiniyah)
            $query->where('TingkatDiniyah', $tingkatDiniyah);

        // Ambil semua data (belum dipaginasi)
        $allSiswa = $query->get();

        // Filter hanya siswa yang memiliki tunggakan atau kelebihan bayar
        $filteredSiswa = $allSiswa->filter(function ($siswa) {
            return !empty($siswa->getKategoriSaldoTidakNol());
        });

        // Paginasi manual
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $currentItems = $filteredSiswa->slice(($page - 1) * $perPage, $perPage)->values();
        $siswaList = new LengthAwarePaginator($currentItems, $filteredSiswa->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        // Data untuk dropdown filter
        $unitFormalList = Siswa::whereNotNull('UnitFormal')->distinct()->pluck('UnitFormal');
        $asramaPondokList = Siswa::whereNotNull('AsramaPondok')->distinct()->pluck('AsramaPondok');
        $tingkatDiniyahList = Siswa::whereNotNull('TingkatDiniyah')->distinct()->pluck('TingkatDiniyah');

        // Proses grouping saldo tidak nol per periode untuk setiap siswa
        foreach ($siswaList as $siswa) {
            $kategoriTidakNol = $siswa->getKategoriSaldoTidakNol();
            $grouped = [];
            foreach ($kategoriTidakNol as $item) {
                $periode = $item['periode'];
                if (!isset($grouped[$periode])) {
                    $grouped[$periode] = [];
                }
                $grouped[$periode][] = $item;
            }
            $siswa->tunggakan_per_periode = $grouped;
            $siswa->total_tunggakan = $siswa->getTotalTunggakan();
            $siswa->total_overpaid = $siswa->getTotalOverpaid();
        }

        return view('custom.index', compact(
            'siswaList',
            'keyword',
            'unitFormal',
            'asramaPondok',
            'tingkatDiniyah',
            'unitFormalList',
            'asramaPondokList',
            'tingkatDiniyahList'
        ));
    }

    public function export(Request $request)
    {
        return Excel::download(new SiswaBelumLunasExport($request), 'siswa_belum_lunas_' . date('Y-m-d_His') . '.xlsx');
    }
}