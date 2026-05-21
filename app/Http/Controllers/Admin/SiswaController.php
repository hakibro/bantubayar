<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SiswaTotalTunggakanExport;
use App\Http\Controllers\Controller;
use App\Models\LembagaKelas;
use App\Models\User;
use App\Models\Siswa;
use App\Services\PembayaranService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $petugas = User::role('petugas')->get();
        $selectedPeriode = $request->get('periode_penanganan', 'bulan_ini');
        [$periodStart, $periodEnd] = $this->resolvePeriodePenanganan($selectedPeriode);
        $applyPeriod = function ($q) use ($periodStart, $periodEnd) {
            if ($periodStart && $periodEnd) {
                $q->whereBetween('penanganan.created_at', [$periodStart, $periodEnd]);
            }

            return $q;
        };

        $query = $this->baseSiswaQuery($request)
            ->leftJoin('v_status_lunas_siswa as sl', 'sl.idperson', '=', 'v_siswa.idperson')
            ->select('v_siswa.*', 'sl.is_lunas', 'sl.total_tunggakan')
            ->with([
                'petugas' => function ($q) {
                    $q->limit(1);
                }
            ])
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

        $baseStatQuery = $this->baseSiswaQuery($request);
        $statPenanganan = [
            'semua' => (clone $baseStatQuery)->count(),
            'sudah_ditangani' => (clone $baseStatQuery)->whereHas('penanganan', $applyPeriod)->count(),
            'belum_ditangani' => (clone $baseStatQuery)->whereDoesntHave('penanganan', $applyPeriod)->count(),
            'aktif' => (clone $baseStatQuery)->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', '!=', 'selesai'))->count(),
            'selesai' => (clone $baseStatQuery)->whereHas('penanganan', fn($q) => $applyPeriod($q)->where('status', 'selesai'))->count(),
        ];
        $petugasPerformance = $this->getPetugasPerformance($request, $applyPeriod);

        $this->applySort($query, $request->get('sort'));

        $siswa = $query->paginate(40)->withQueryString();

        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html' => view('admin.siswa.partials.table', compact('siswa', 'petugas'))->render(),
                'statPenanganan' => $statPenanganan,
                'petugasPerformanceHtml' => view('admin.siswa.partials.petugas-performance', compact('petugasPerformance'))->render(),
            ]);
        }

        $daftarLembaga = $this->getLembagaOptions();

        return view('admin.siswa.index', compact('siswa', 'petugas', 'daftarLembaga', 'statPenanganan', 'petugasPerformance'));
    }

    public function export(Request $request)
    {
        $selectedPeriode = $request->get('periode_penanganan', 'bulan_ini');
        [$periodStart, $periodEnd] = $this->resolvePeriodePenanganan($selectedPeriode);
        $applyPeriod = function ($q) use ($periodStart, $periodEnd) {
            if ($periodStart && $periodEnd) {
                $q->whereBetween('penanganan.created_at', [$periodStart, $periodEnd]);
            }

            return $q;
        };

        $query = $this->baseSiswaQuery($request);

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

        $this->applySort($query, $request->get('sort'), 'sl_filter');

        return Excel::download(new SiswaTotalTunggakanExport($query), 'data-siswa-total-tunggakan.xlsx');
    }

    private function applySort(Builder $query, ?string $sort, string $statusAlias = 'sl'): Builder
    {
        if ($sort === 'tagihan_desc') {
            return $query
                ->orderByRaw("COALESCE({$statusAlias}.total_tunggakan, 0) DESC")
                ->orderBy('v_siswa.nama');
        }

        return $query->orderBy('v_siswa.nama');
    }

    private function baseSiswaQuery(Request $request): Builder
    {
        $query = Siswa::query()
            ->leftJoin('v_status_lunas_siswa as sl_filter', 'sl_filter.idperson', '=', 'v_siswa.idperson');

        if ($request->filled('search')) {
            $qsearch = $request->search;
            $query->where(function ($q) use ($qsearch) {
                $q->where('v_siswa.nama', 'like', "%{$qsearch}%")
                    ->orWhere('v_siswa.idperson', 'like', "%{$qsearch}%");
            });
        }

        if ($request->filled('lembaga_filter')) {
            [$type, $value] = array_pad(explode(':', $request->lembaga_filter, 2), 2, null);

            if ($type === 'formal') {
                $query->where('v_siswa.unit_formal', $value);
            } elseif ($type === 'pondok') {
                $query->where('v_siswa.AsramaPondok', $value);
            } elseif ($type === 'diniyah') {
                $query->where('v_siswa.TingkatMadin', $value);
            }
        }

        if ($request->filled('tagihan_range')) {
            $query->where(function ($q) use ($request) {
                $totalTunggakan = 'COALESCE(sl_filter.total_tunggakan, 0)';

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

    private function getLembagaOptions(): array
    {
        $formal = LembagaKelas::formal()
            ->distinct()
            ->orderBy('title')
            ->pluck('title');

        $pondok = LembagaKelas::asrama()
            ->distinct()
            ->orderBy('idtingkat')
            ->pluck('idtingkat');

        $diniyah = LembagaKelas::madin()
            ->distinct()
            ->orderBy('idtingkat')
            ->pluck('idtingkat');

        return [
            'formal' => $formal,
            'pondok' => $pondok,
            'diniyah' => $diniyah,
        ];
    }

    private function getPetugasPerformance(Request $request, callable $applyPeriod)
    {
        $studentSubQuery = $this->baseSiswaQuery($request)->select('v_siswa.idperson');

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

    public function kelas(Request $request)
    {
        $kelas = LembagaKelas::formal()
            ->when($request->lembaga, fn($q) => $q->where('title', $request->lembaga))
            ->distinct()
            ->orderBy('keterangan')
            ->pluck('keterangan');

        return response()->json($kelas);
    }

    public function kamar(Request $request)
    {
        $kamar = LembagaKelas::asrama()
            ->when($request->asrama, fn($q) => $q->where('idtingkat', $request->asrama))
            ->distinct()
            ->orderBy('idrombel')
            ->pluck('idrombel');

        return response()->json($kamar);
    }

    public function kelasDiniyah(Request $request)
    {
        $kelasDiniyah = LembagaKelas::madin()
            ->when($request->diniyah, fn($q) => $q->where('idtingkat', $request->diniyah))
            ->distinct()
            ->orderBy('idrombel')
            ->pluck('idrombel');

        return response()->json($kelasDiniyah);
    }

    public function show($id, PembayaranService $pembayaranService)
    {
        $siswa = Siswa::with(['statusLunas', 'phone'])->findOrFail($id);
        $pembayaranService->refreshStatusLunasSiswa((string) $siswa->idperson);
        $siswa->load('statusLunas');

        $summary = $pembayaranService->getSummaryPerPeriode((string) $siswa->idperson);
        $belumLunas = $pembayaranService->getDetailBelumLunas((string) $siswa->idperson);
        $totalTunggakan = $pembayaranService->getTotalBelumLunas((string) $siswa->idperson);
        $penangananList = $siswa->penanganan()
            ->with(['petugas', 'histories' => fn($query) => $query->latest(), 'kesanggupanTerakhir'])
            ->latest()
            ->get();
        $penangananAktif = $penangananList->first(fn($item) => $item->status !== 'selesai');

        return view('admin.siswa.show', compact(
            'siswa',
            'summary',
            'belumLunas',
            'totalTunggakan',
            'penangananList',
            'penangananAktif'
        ));
    }
}
