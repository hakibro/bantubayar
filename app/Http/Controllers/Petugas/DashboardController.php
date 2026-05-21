<?php

namespace App\Http\Controllers\Petugas;

use App\Exports\SiswaTotalTunggakanExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $lembagaUser = $user->lembaga;

        $baseSiswaQuery = Siswa::query();

        if ($user->hasRole('petugas')) {
            $baseSiswaQuery->whereHas('petugas', function ($q) {
                $q->where('users.id', Auth::id());
            });
        } else {
            $baseSiswaQuery->where(function ($q) use ($lembagaUser) {
                $q->where('unit_formal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatMadin', $lembagaUser);
            });
        }

        $this->applyTagihanRange($baseSiswaQuery, $request->get('tagihan_range'));

        $range = $request->get('range', 'current_week');
        $scope = $user->penanganan()->with('siswa:idperson,nama');
        $filteredSiswaIds = (clone $baseSiswaQuery)->select('v_siswa.idperson');
        $scope->whereIn('penanganan.id_siswa', $filteredSiswaIds);

        $statsQuery = (clone $scope);
        $startDate = null;
        $endDate = now();

        if ($range === 'current_week') {
            $startDate = now()->startOfWeek();
            $statsQuery->where('updated_at', '>=', $startDate);
        } elseif ($range === 'last_week') {
            $startDate = now()->subWeek()->startOfWeek();
            $endDate = now()->subWeek()->endOfWeek();
            $statsQuery->whereBetween('updated_at', [$startDate, $endDate]);
        } elseif ($range === 'current_month') {
            $startDate = now()->startOfMonth();
            $statsQuery->where('updated_at', '>=', $startDate);
        } elseif ($range === 'older') {
            $statsQuery->where('updated_at', '<', now()->subWeek()->startOfWeek());
        } elseif ($range === 'all') {
            $startDate = null;
        }

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

        $applySelectedPeriod = function ($query, string $column = 'updated_at') use ($range, $startDate, $endDate) {
            if ($range === 'older') {
                return $query->where($column, '<', now()->subWeek()->startOfWeek());
            }

            if ($startDate) {
                return $query->whereBetween($column, [$startDate, $endDate]);
            }

            return $query;
        };

        $lunasSiswaQuery = (clone $baseSiswaQuery)
            ->join('v_status_lunas_siswa as sl_lunas', 'sl_lunas.idperson', '=', 'v_siswa.idperson')
            ->where('sl_lunas.is_lunas', 1);

        $belumLunasSiswaQuery = (clone $baseSiswaQuery)
            ->join('v_status_lunas_siswa as sl_belum_lunas', 'sl_belum_lunas.idperson', '=', 'v_siswa.idperson')
            ->where('sl_belum_lunas.is_lunas', 0);

        $statistikSiswa = [
            'total_siswa' => (clone $baseSiswaQuery)->count(),
            'lunas' => (clone $lunasSiswaQuery)->count(),
            'belum_lunas' => (clone $belumLunasSiswaQuery)->count(),
            'sudah_ditangani' => (clone $baseSiswaQuery)
                ->whereHas('penanganan', fn($q) => $applySelectedPeriod($q, 'penanganan.updated_at')->where('id_petugas', $user->id))
                ->count(),
            'belum_ditangani' => (clone $baseSiswaQuery)
                ->whereDoesntHave('penanganan', fn($q) => $applySelectedPeriod($q, 'penanganan.updated_at')->where('id_petugas', $user->id))
                ->count(),
        ];

        $penangananPeriodQuery = $applySelectedPeriod((clone $scope), 'penanganan.updated_at');
        $statistikPenanganan = [
            'total' => (clone $penangananPeriodQuery)->count(),
            'aktif' => (clone $penangananPeriodQuery)->where('status', '!=', 'selesai')->count(),
            'menunggu_respon' => $summary['menunggu_respon'],
            'menunggu_tindak_lanjut' => $summary['menunggu_tindak_lanjut'],
            'selesai' => $summary['selesai'],
            'terlambat' => 0,
        ];

        $tugasAktif = (clone $scope)
            ->whereIn('status', ['menunggu_respon', 'menunggu_tindak_lanjut'])
            ->orderBy('updated_at', 'asc')
            ->paginate(10);

        $tugasAktif->through(function ($item) {
            $item->lama_menunggu = $item->updated_at->diffForHumans();
            return $item;
        });

        $penangananTerlambat = (clone $scope)
            ->where(function ($q) {
                $q->where(function ($query) {
                    $query->where('status', 'menunggu_respon')
                        ->where('updated_at', '<=', now()->subDays(2));
                })->orWhere(function ($query) {
                    $query->where('status', 'menunggu_tindak_lanjut')
                        ->where('updated_at', '<=', now()->subDays(3));
                });
            })
            ->get();

        $statistikPenanganan['terlambat'] = $penangananTerlambat->count();

        $ratingQuery = (clone $scope)->whereNotNull('rating');

        if ($range === 'older') {
            $ratingQuery->where('updated_at', '<', now()->subWeek()->startOfWeek());
        } elseif ($startDate) {
            $ratingQuery->whereBetween('updated_at', [$startDate, $endDate]);
        }

        $statistikRespon = [
            'rata_rata' => round((float) (clone $ratingQuery)->avg('rating'), 1),
            'total_dinilai' => (clone $ratingQuery)->count(),
            'responsif' => (clone $ratingQuery)->where('rating', '>=', 4)->count(),
        ];

        $catatanTerbaru = (clone $ratingQuery)->latest('updated_at')->take(3)->get();

        if ($request->ajax()) {
            return view('petugas.dashboard.partials.cards', compact('summary', 'range', 'statistikSiswa', 'statistikPenanganan'));
        }

        return view('petugas.dashboard.index', compact(
            'summary',
            'statistikSiswa',
            'statistikPenanganan',
            'tugasAktif',
            'penangananTerlambat',
            'statistikRespon',
            'catatanTerbaru',
            'range'
        ));
    }

    public function exportSiswa(Request $request)
    {
        $query = $this->scopedSiswaQuery($request);

        return Excel::download(new SiswaTotalTunggakanExport($query), 'data-siswa-total-tunggakan.xlsx');
    }

    private function scopedSiswaQuery(Request $request)
    {
        $user = auth()->user();
        $lembagaUser = $user->lembaga;

        $query = Siswa::query();

        if ($user->hasRole('petugas')) {
            $query->whereHas('petugas', function ($q) {
                $q->where('users.id', Auth::id());
            });
        } else {
            $query->where(function ($q) use ($lembagaUser) {
                $q->where('unit_formal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatMadin', $lembagaUser);
            });
        }

        $this->applyTagihanRange($query, $request->get('tagihan_range'));

        return $query;
    }

    private function applyTagihanRange($query, ?string $range)
    {
        if (!$range) {
            return $query;
        }

        $query->leftJoin('v_status_lunas_siswa as sl_dashboard_filter', 'sl_dashboard_filter.idperson', '=', 'v_siswa.idperson')
            ->where(function ($q) use ($range) {
                $totalTunggakan = 'COALESCE(sl_dashboard_filter.total_tunggakan, 0)';

                if ($range === '0') {
                    $q->whereRaw("{$totalTunggakan} = 0");
                } elseif ($range === '1_500k') {
                    $q->whereRaw("{$totalTunggakan} > 0 AND {$totalTunggakan} <= 500000");
                } elseif ($range === '500k_1jt') {
                    $q->whereRaw("{$totalTunggakan} > 500000 AND {$totalTunggakan} <= 1000000");
                } elseif ($range === '1jt_2jt') {
                    $q->whereRaw("{$totalTunggakan} > 1000000 AND {$totalTunggakan} <= 2000000");
                } elseif ($range === '2jt_plus') {
                    $q->whereRaw("{$totalTunggakan} > 2000000");
                }
            });

        return $query;
    }
}
