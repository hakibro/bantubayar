<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPetugasController extends Controller
{
    /**
     * Tampilkan halaman laporan aktivitas petugas/bendahara.
     */
    public function index(Request $request)
    {
        $range = $request->input('range', 'current_month');
        $petugasId = $request->input('petugas_id');

        [$periodStart, $periodEnd, $startDate, $endDate, $periodLabel] = $this->resolvePeriod($range, $request);

        if (!in_array($range, ['current_week', 'last_week', 'current_month', 'previous_month', 'all', 'custom'])) {
            $range = 'current_month';
        }

        $dateRange = fn($query) => $periodStart && $periodEnd
            ? $query->whereBetween('penanganan.created_at', [$periodStart, $periodEnd])
            : $query;

        $petugasList = User::role(['petugas', 'bendahara'])
            ->with('roles:id,name')
            ->orderBy('name')
            ->get();

        $baseQuery = $dateRange(Penanganan::query()
            ->leftJoin('v_status_lunas_siswa as sl_report', 'sl_report.idperson', '=', 'penanganan.id_siswa'))
            ->when($petugasId, fn($query) => $query->where('penanganan.id_petugas', $petugasId));
        $isPenangananTunggakan = 'COALESCE(JSON_LENGTH(penanganan.jenis_pembayaran), 0) > 0';

        $summary = (clone $baseQuery)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN penanganan.status = 'selesai' THEN 1 END) as selesai,
                COUNT(CASE WHEN {$isPenangananTunggakan} THEN 1 END) as total_tunggakan,
                COUNT(CASE WHEN NOT ({$isPenangananTunggakan}) THEN 1 END) as total_apresiasi,
                COUNT(CASE WHEN {$isPenangananTunggakan} AND penanganan.status = 'selesai' THEN 1 END) as selesai_tunggakan,
                COUNT(CASE WHEN {$isPenangananTunggakan} AND penanganan.status != 'selesai' THEN 1 END) as aktif_tunggakan,
                COUNT(CASE WHEN NOT ({$isPenangananTunggakan}) AND penanganan.status = 'selesai' THEN 1 END) as selesai_apresiasi,
                COUNT(CASE WHEN NOT ({$isPenangananTunggakan}) AND penanganan.status != 'selesai' THEN 1 END) as aktif_apresiasi,
                COUNT(CASE WHEN penanganan.status = 'menunggu_respon' THEN 1 END) as menunggu_respon,
                COUNT(CASE WHEN penanganan.status = 'menunggu_tindak_lanjut' THEN 1 END) as menunggu_tindak_lanjut,
                COUNT(CASE WHEN penanganan.rating IS NOT NULL THEN 1 END) as total_dinilai,
                ROUND(AVG(CASE WHEN penanganan.rating IS NOT NULL THEN penanganan.rating END), 2) as rating_avg,
                ROUND(AVG(CASE WHEN penanganan.status = 'selesai' THEN TIMESTAMPDIFF(HOUR, penanganan.created_at, penanganan.updated_at) END), 1) as rata_jam_selesai
            ")
            ->first();

        $totalPenanganan = (int) ($summary->total ?? 0);
        $selesai = (int) ($summary->selesai ?? 0);
        $totalTunggakan = (int) ($summary->total_tunggakan ?? 0);
        $totalApresiasi = (int) ($summary->total_apresiasi ?? 0);
        $selesaiTunggakan = (int) ($summary->selesai_tunggakan ?? 0);
        $aktifTunggakan = (int) ($summary->aktif_tunggakan ?? 0);
        $selesaiApresiasi = (int) ($summary->selesai_apresiasi ?? 0);
        $aktifApresiasi = (int) ($summary->aktif_apresiasi ?? 0);
        $menungguRespon = (int) ($summary->menunggu_respon ?? 0);
        $menungguTindakLanjut = (int) ($summary->menunggu_tindak_lanjut ?? 0);
        $ratingAvg = (float) ($summary->rating_avg ?? 0);
        $totalDinilai = (int) ($summary->total_dinilai ?? 0);
        $rataJamSelesai = (float) ($summary->rata_jam_selesai ?? 0);
        $completionRate = round(($selesai / max($totalPenanganan, 1)) * 100, 1);
        $tunggakanSuccessRate = round(($selesaiTunggakan / max($totalTunggakan, 1)) * 100, 1);
        $apresiasiCompletionRate = round(($selesaiApresiasi / max($totalApresiasi, 1)) * 100, 1);

        $statusBreakdown = [
            'selesai' => $selesai,
            'menunggu_respon' => $menungguRespon,
            'menunggu_tindak_lanjut' => $menungguTindakLanjut,
        ];

        $hasilBreakdown = (clone $baseQuery)
            ->where('penanganan.status', 'selesai')
            ->selectRaw("
                CASE WHEN {$isPenangananTunggakan} THEN 'tunggakan' ELSE 'apresiasi' END as tipe_penanganan,
                COALESCE(penanganan.hasil, 'tanpa_hasil') as hasil,
                COUNT(*) as total
            ")
            ->groupByRaw("CASE WHEN {$isPenangananTunggakan} THEN 'tunggakan' ELSE 'apresiasi' END")
            ->groupByRaw("COALESCE(penanganan.hasil, 'tanpa_hasil')")
            ->orderByDesc('total')
            ->get()
            ->groupBy('tipe_penanganan');

        $performanceStats = (clone $baseQuery)
            ->selectRaw("
                penanganan.id_petugas,
                COUNT(penanganan.id) as total,
                COUNT(CASE WHEN penanganan.status = 'selesai' THEN 1 END) as selesai,
                COUNT(CASE WHEN penanganan.status != 'selesai' THEN 1 END) as aktif,
                COUNT(CASE WHEN {$isPenangananTunggakan} THEN 1 END) as total_tunggakan,
                COUNT(CASE WHEN {$isPenangananTunggakan} AND penanganan.status = 'selesai' THEN 1 END) as selesai_tunggakan,
                COUNT(CASE WHEN {$isPenangananTunggakan} AND penanganan.status != 'selesai' THEN 1 END) as aktif_tunggakan,
                COUNT(CASE WHEN NOT ({$isPenangananTunggakan}) THEN 1 END) as total_apresiasi,
                COUNT(CASE WHEN NOT ({$isPenangananTunggakan}) AND penanganan.status = 'selesai' THEN 1 END) as selesai_apresiasi
            ")
            ->groupBy('penanganan.id_petugas')
            ->get()
            ->keyBy('id_petugas');

        $performanceUsers = $petugasId
            ? $petugasList->where('id', (int) $petugasId)->values()
            : $petugasList;

        $petugasPerformance = $performanceUsers
            ->map(function ($user) use ($performanceStats, $totalPenanganan) {
                $stats = $performanceStats->get($user->id);
                $related = $this->relatedStudentCounts($user);

                $item = (object) [
                    'id' => $user->id,
                    'name' => $user->name,
                    'lembaga' => $user->lembaga,
                    'roles_label' => $user->roles->pluck('name')->map(fn($role) => str($role)->title())->implode(', '),
                    'total' => (int) ($stats->total ?? 0),
                    'selesai' => (int) ($stats->selesai ?? 0),
                    'aktif' => (int) ($stats->aktif ?? 0),
                    'total_tunggakan' => (int) ($stats->total_tunggakan ?? 0),
                    'selesai_tunggakan' => (int) ($stats->selesai_tunggakan ?? 0),
                    'aktif_tunggakan' => (int) ($stats->aktif_tunggakan ?? 0),
                    'total_apresiasi' => (int) ($stats->total_apresiasi ?? 0),
                    'selesai_apresiasi' => (int) ($stats->selesai_apresiasi ?? 0),
                    'related_siswa' => $related['total'],
                    'related_tunggakan_siswa' => $related['tunggakan'],
                ];

                $item->completion_rate = round(($item->selesai / max($item->total, 1)) * 100, 1);
                $item->tunggakan_success_rate = round(($item->selesai_tunggakan / max($item->total_tunggakan, 1)) * 100, 1);
                $item->tunggakan_coverage_rate = round(($item->total_tunggakan / max($item->related_tunggakan_siswa, 1)) * 100, 1);
                $item->student_coverage_rate = round(($item->total / max($item->related_siswa, 1)) * 100, 1);
                $item->activity_share = round(($item->total / max($totalPenanganan, 1)) * 100, 1);

                return $item;
            })
            ->sortBy([
                ['total', 'desc'],
                ['selesai_tunggakan', 'desc'],
                ['name', 'asc'],
            ])
            ->values();

        $petugasDetailIds = $petugasPerformance->pluck('id');
        $penangananPetugas = $petugasDetailIds->isEmpty()
            ? collect()
            : (clone $baseQuery)
                ->whereIn('penanganan.id_petugas', $petugasDetailIds)
                ->select(
                    'penanganan.*',
                    DB::raw('COALESCE(sl_report.total_tunggakan, 0) as total_tunggakan_siswa'),
                    DB::raw("CASE WHEN {$isPenangananTunggakan} THEN 1 ELSE 0 END as is_penanganan_tunggakan")
                )
                ->with([
                    'siswa:idperson,nama',
                    'kesanggupanTerakhir',
                    'histories' => fn($query) => $query->latest(),
                ])
                ->withCount('histories')
                ->latest('penanganan.created_at')
                ->get()
                ->groupBy('id_petugas');

        $selectedPetugas = $petugasId
            ? $petugasList->firstWhere('id', (int) $petugasId)
            : null;

        return view('admin.laporan.petugas', compact(
            'petugasList',
            'selectedPetugas',
            'range',
            'periodLabel',
            'startDate',
            'endDate',
            'petugasId',
            'totalPenanganan',
            'selesai',
            'totalTunggakan',
            'totalApresiasi',
            'selesaiTunggakan',
            'aktifTunggakan',
            'selesaiApresiasi',
            'aktifApresiasi',
            'menungguRespon',
            'menungguTindakLanjut',
            'ratingAvg',
            'totalDinilai',
            'rataJamSelesai',
            'completionRate',
            'tunggakanSuccessRate',
            'apresiasiCompletionRate',
            'statusBreakdown',
            'hasilBreakdown',
            'petugasPerformance',
            'penangananPetugas'
        ));
    }

    private function relatedStudentCounts(User $user): array
    {
        $query = Siswa::query()
            ->leftJoin('v_status_lunas_siswa as sl_related', 'sl_related.idperson', '=', 'v_siswa.idperson');

        if ($user->hasRole('bendahara')) {
            if (!$user->lembaga) {
                return ['total' => 0, 'tunggakan' => 0];
            }

            $query->where(function ($q) use ($user) {
                $q->where('v_siswa.unit_formal', $user->lembaga)
                    ->orWhere('v_siswa.AsramaPondok', $user->lembaga)
                    ->orWhere('v_siswa.TingkatMadin', $user->lembaga);
            });
        } else {
            $query->whereHas('petugas', fn($q) => $q->where('users.id', $user->id));
        }

        return [
            'total' => (clone $query)->count('v_siswa.idperson'),
            'tunggakan' => (clone $query)->whereRaw('COALESCE(sl_related.total_tunggakan, 0) > 0')->count('v_siswa.idperson'),
        ];
    }

    private function resolvePeriod(string $range, Request $request): array
    {
        $today = now();

        if ($range === 'all') {
            return [null, null, null, null, 'Semua periode'];
        }

        if ($range === 'current_week') {
            $start = $today->copy()->startOfWeek();
            $end = $today->copy()->endOfDay();
        } elseif ($range === 'last_week') {
            $start = $today->copy()->subWeek()->startOfWeek();
            $end = $today->copy()->subWeek()->endOfWeek();
        } elseif ($range === 'previous_month') {
            $start = $today->copy()->subMonthNoOverflow()->startOfMonth();
            $end = $today->copy()->subMonthNoOverflow()->endOfMonth();
        } elseif ($range === 'custom') {
            $start = Carbon::parse($request->input('start_date', $today->copy()->startOfMonth()->format('Y-m-d')))->startOfDay();
            $end = Carbon::parse($request->input('end_date', $today->format('Y-m-d')))->endOfDay();
        } else {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfDay();
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [
            $start,
            $end,
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
            $start->translatedFormat('d F Y') . ' - ' . $end->translatedFormat('d F Y'),
        ];
    }

    /**
     * Export laporan ke PDF (opsional)
     */
    public function exportPdf(Request $request)
    {
        // Nanti bisa ditambahkan
    }
}
