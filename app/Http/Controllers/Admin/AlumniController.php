<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AlumniTotalTunggakanExport;
use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Services\PembayaranService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $selectedPeriode = $request->get('periode_penanganan', 'bulan_ini');
        [$periodStart, $periodEnd] = $this->resolvePeriodePenanganan($selectedPeriode);
        $applyPeriod = function ($q) use ($periodStart, $periodEnd) {
            if ($periodStart && $periodEnd) {
                $q->whereBetween('penanganan.created_at', [$periodStart, $periodEnd]);
            }

            return $q;
        };

        $query = $this->baseAlumniQuery($request)
            ->leftJoin('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_alumni.idperson')
            ->select('v_alumni.*', 'sl.is_lunas', 'sl.total_tunggakan')
            ->with(['latestPenanganan.petugas'])
            ->withCount([
                'penanganan as jumlah_penanganan',
                'penanganan as penanganan_aktif_count' => fn($q) => $q->where('status', '!=', 'selesai'),
                'penanganan as penanganan_selesai_count' => fn($q) => $q->where('status', 'selesai'),
            ]);

        if ($request->filled('status_penanganan')) {
            if ($request->status_penanganan === 'sudah_ditangani') {
                $query->whereHas('penanganan', $applyPeriod);
            } elseif ($request->status_penanganan === 'belum_ditangani') {
                $query->whereDoesntHave('penanganan', $applyPeriod);
            } elseif ($request->status_penanganan === 'aktif') {
                $query->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', '!=', 'selesai'));
            } elseif ($request->status_penanganan === 'selesai') {
                $query->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', 'selesai'));
            }
        }

        $baseStatQuery = $this->baseAlumniQuery($request);
        $statPenanganan = [
            'semua' => (clone $baseStatQuery)->count(),
            'sudah_ditangani' => (clone $baseStatQuery)->whereHas('penanganan', $applyPeriod)->count(),
            'belum_ditangani' => (clone $baseStatQuery)->whereDoesntHave('penanganan', $applyPeriod)->count(),
            'aktif' => (clone $baseStatQuery)->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', '!=', 'selesai'))->count(),
            'selesai' => (clone $baseStatQuery)->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', 'selesai'))->count(),
        ];
        $petugasPerformance = $this->getPetugasPerformance($request, $applyPeriod);

        $this->applySort($query, $request->get('sort'));

        $alumni = $query->paginate(40)->withQueryString();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html' => view('admin.alumni.partials.table', compact('alumni'))->render(),
                'statPenanganan' => $statPenanganan,
                'petugasPerformanceHtml' => view('admin.alumni.partials.petugas-performance', compact('petugasPerformance'))->render(),
            ]);
        }

        $daftarLembaga = Alumni::query()
            ->distinct()
            ->whereNotNull('lembaga')
            ->orderBy('lembaga')
            ->pluck('lembaga');

        return view('admin.alumni.index', compact('alumni', 'daftarLembaga', 'statPenanganan', 'petugasPerformance'));
    }

    public function export(Request $request)
    {
        $query = $this->baseAlumniQuery($request)
            ->leftJoin('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_alumni.idperson')
            ->select('v_alumni.*', 'sl.is_lunas', 'sl.total_tunggakan');

        if ($request->filled('status_penanganan')) {
            $selectedPeriode = $request->get('periode_penanganan', 'bulan_ini');
            [$periodStart, $periodEnd] = $this->resolvePeriodePenanganan($selectedPeriode);
            $applyPeriod = function ($q) use ($periodStart, $periodEnd) {
                if ($periodStart && $periodEnd) {
                    $q->whereBetween('penanganan.created_at', [$periodStart, $periodEnd]);
                }

                return $q;
            };

            if ($request->status_penanganan === 'sudah_ditangani') {
                $query->whereHas('penanganan', $applyPeriod);
            } elseif ($request->status_penanganan === 'belum_ditangani') {
                $query->whereDoesntHave('penanganan', $applyPeriod);
            } elseif ($request->status_penanganan === 'aktif') {
                $query->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', '!=', 'selesai'));
            } elseif ($request->status_penanganan === 'selesai') {
                $query->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', 'selesai'));
            }
        }

        $this->applySort($query, $request->get('sort'));

        return Excel::download(new AlumniTotalTunggakanExport($query), 'data-alumni-total-tunggakan.xlsx');
    }

    public function show(Request $request, PembayaranService $pembayaranService, $idperson)
    {
        $alumni = Alumni::findOrFail($idperson);
        $pembayaranService->refreshStatusLunasSiswa((string) $alumni->idperson);

        $penanganan = $alumni->penanganan()
            ->with('petugas')
            ->orderBy('created_at', 'desc')
            ->get();

        $penangananTerakhir = $penanganan->first();
        $riwayatAksi = $penangananTerakhir
            ? $penangananTerakhir->histories()->latest()->get()
            : collect();

        $summary = $pembayaranService->getSummaryPerPeriode((string) $alumni->idperson);
        $detailPembayaran = $pembayaranService->getDetailPembayaran((string) $alumni->idperson);
        $belumLunas = $pembayaranService->getDetailBelumLunas((string) $alumni->idperson);
        $totalTunggakan = $pembayaranService->getTotalBelumLunas((string) $alumni->idperson);

        $siswa = $alumni;
        $isAlumni = true;
        $petugasLogin = auth()->user();
        $urlUntukWali = null;

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

    private function applySort(Builder $query, ?string $sort): Builder
    {
        if ($sort === 'tagihan_desc') {
            return $query
                ->orderByRaw('COALESCE(sl.total_tunggakan, 0) DESC')
                ->orderBy('v_alumni.nama');
        }

        return $query->orderBy('v_alumni.nama');
    }

    private function baseAlumniQuery(Request $request): Builder
    {
        $query = Alumni::query();

        if ($request->filled('search')) {
            $qsearch = $request->search;
            $query->where(function ($q) use ($qsearch) {
                $q->where('v_alumni.nama', 'like', "%{$qsearch}%")
                    ->orWhere('v_alumni.idperson', 'like', "%{$qsearch}%");
            });
        }

        if ($request->filled('lembaga_filter')) {
            $query->where('v_alumni.lembaga', $request->lembaga_filter);
        }

        if ($request->filled('tahun_lulus')) {
            $query->where('v_alumni.tahun_lulus', $request->tahun_lulus);
        }

        if ($request->filled('tagihan_range')) {
            $query->where(function ($q) use ($request) {
                $totalTunggakan = 'COALESCE(sl.total_tunggakan, 0)';

                if ($request->tagihan_range === '0') {
                    $q->whereRaw("{$totalTunggakan} = 0");
                } elseif ($request->tagihan_range === '1_500k') {
                    $q->whereRaw("{$totalTunggakan} > 0 AND {$totalTunggakan} <= 500000");
                } elseif ($request->tagihan_range === '500k_1jt') {
                    $q->whereRaw("{$totalTunggakan} > 500000 AND {$totalTunggakan} <= 1000000");
                } elseif ($request->tagihan_range === '1jt_2jt') {
                    $q->whereRaw("{$totalTunggakan} > 1000000 AND {$totalTunggakan} <= 2000000");
                } elseif ($request->tagihan_range === '2jt_plus') {
                    $q->whereRaw("{$totalTunggakan} > 2000000");
                }
            });
        }

        return $query;
    }

    private function getPetugasPerformance(Request $request, callable $applyPeriod)
    {
        $studentSubQuery = $this->baseAlumniQuery($request)->select('v_alumni.idperson');

        $query = DB::table('penanganan')
            ->join('users', 'users.id', '=', 'penanganan.id_petugas')
            ->whereNull('penanganan.deleted_at')
            ->whereIn('penanganan.id_siswa', $studentSubQuery);

        $applyPeriod($query);

        if ($request->status_penanganan === 'aktif') {
            $query->where('penanganan.status', '!=', 'selesai');
        } elseif ($request->status_penanganan === 'selesai') {
            $query->where('penanganan.status', 'selesai');
        } elseif ($request->status_penanganan === 'belum_ditangani') {
            return collect();
        }

        $rows = $query
            ->select('users.id', 'users.name', DB::raw('COUNT(*) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        $grandTotal = max(1, (int) $rows->sum('total'));

        return $rows->map(function ($row) use ($grandTotal) {
            $row->percentage = round(($row->total / $grandTotal) * 100, 1);
            return $row;
        });
    }

    private function resolvePeriodePenanganan(?string $periode): array
    {
        return match ($periode) {
            'minggu_ini' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'minggu_lalu' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'bulan_ini' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'sebelumnya' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth()],
            default => [null, null],
        };
    }
}
