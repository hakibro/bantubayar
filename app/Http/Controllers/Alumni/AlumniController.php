<?php

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Penanganan;
use App\Services\PembayaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $lembagaUser = auth()->user()->lembaga;

        // Daftar alumni memakai total tunggakan dari cache v_status_lunas_siswa
        // (di-refresh scheduler tiap 6 jam) — pola hibrida seperti menu Siswa.
        // Halaman detail alumni tetap menghitung live dari ips_siswa.
        $query = Alumni::query()
            ->select('v_alumni.*', 'sl.total_tunggakan', 'sl.is_lunas')
            ->leftJoin('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_alumni.idperson')
            ->where('lembaga', $lembagaUser);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', '%' . $keyword . '%')
                    ->orWhere('idperson', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->tahun_lulus);
        }

        // Filter pengelompokan range tunggakan.
        if ($request->filled('range_tunggakan')) {
            $this->applyTunggakanRange($query, $request->range_tunggakan);
        } else {
            // Default menu ini: hanya alumni yang masih punya tunggakan (> 0).
            $query->where('sl.total_tunggakan', '>', 0);
        }

        // Urutan (sort) berdasarkan tunggakan atau nama.
        $sort = $request->get('sort');
        if ($sort === 'tunggakan_desc') {
            $query->orderByDesc('sl.total_tunggakan')->orderBy('nama');
        } elseif ($sort === 'tunggakan_asc') {
            $query->orderBy('sl.total_tunggakan')->orderBy('nama');
        } else {
            $query->orderBy('nama', 'asc');
        }

        $alumni = $query->paginate(40)->withQueryString();

        // Daftar tahun kelulusan (menurun) untuk opsi filter.
        $tahunLulusOptions = Alumni::query()
            ->where('lembaga', $lembagaUser)
            ->whereNotNull('tahun_lulus')
            ->distinct()
            ->orderByDesc('tahun_lulus')
            ->pluck('tahun_lulus');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('alumni.partials.list-alumni', compact('alumni'))->render(),
                'pagination' => $alumni->links()->toHtml(),
            ]);
        }

        return view('alumni.index', compact('alumni', 'tahunLulusOptions'));
    }

    /**
     * Terapkan filter range tunggakan pada kolom sl.total_tunggakan (cache).
     */
    private function applyTunggakanRange($query, string $range): void
    {
        switch ($range) {
            case '0':
                $query->where('sl.total_tunggakan', '=', 0);
                break;
            case '1_500k':
                $query->whereBetween('sl.total_tunggakan', [1, 500000]);
                break;
            case '500k_1jt':
                $query->whereBetween('sl.total_tunggakan', [500001, 1000000]);
                break;
            case '1jt_2jt':
                $query->whereBetween('sl.total_tunggakan', [1000001, 2000000]);
                break;
            case '2jt_plus':
                $query->where('sl.total_tunggakan', '>', 2000000);
                break;
        }
    }

    public function show(Request $request, PembayaranService $pembayaranService, $idperson)
    {
        $alumni = Alumni::findOrFail($idperson);
        $pembayaranService->refreshStatusLunasSiswa((string) $alumni->idperson);

        $penanganan = Penanganan::where('id_siswa', $idperson)
            ->with('petugas')
            ->orderBy('created_at', 'desc')
            ->get();

        $urlUntukWali = URL::temporarySignedRoute(
            'penanganan.show',
            now()->addDays(30),
            ['id_siswa' => $alumni->idperson]
        );

        $penangananTerakhir = $penanganan->first();
        $riwayatAksi = $penangananTerakhir
            ? $penangananTerakhir->histories()->latest()->get()
            : collect();

        $petugasLogin = Auth::user();
        $summary = $pembayaranService->getSummaryPerPeriode((string) $alumni->idperson);
        $detailPembayaran = $pembayaranService->getDetailPembayaran((string) $alumni->idperson);
        $belumLunas = $pembayaranService->getDetailBelumLunas((string) $alumni->idperson);
        $totalTunggakan = $pembayaranService->getTotalBelumLunas((string) $alumni->idperson);

        // Reuse tampilan penanganan dengan data alumni sebagai "siswa".
        $siswa = $alumni;
        $isAlumni = true;

        return view(
            'penanganan.show',
            compact(
                'siswa',
                'isAlumni',
                'penanganan',
                'riwayatAksi',
                'penangananTerakhir',
                'urlUntukWali',
                'petugasLogin',
                'summary',
                'detailPembayaran',
                'belumLunas',
                'totalTunggakan'
            )
        );
    }
}
