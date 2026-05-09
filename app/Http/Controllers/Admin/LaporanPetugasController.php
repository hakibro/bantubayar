<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
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

        $baseQuery = $dateRange(Penanganan::query())
            ->when($petugasId, fn($query) => $query->where('penanganan.id_petugas', $petugasId));

        $summary = (clone $baseQuery)
            ->selectRaw("
                COUNT(*) as total,
                COUNT(CASE WHEN penanganan.status = 'selesai' THEN 1 END) as selesai,
                COUNT(CASE WHEN penanganan.status = 'menunggu_respon' THEN 1 END) as menunggu_respon,
                COUNT(CASE WHEN penanganan.status = 'menunggu_tindak_lanjut' THEN 1 END) as menunggu_tindak_lanjut,
                COUNT(CASE WHEN penanganan.rating IS NOT NULL THEN 1 END) as total_dinilai,
                ROUND(AVG(CASE WHEN penanganan.rating IS NOT NULL THEN penanganan.rating END), 2) as rating_avg,
                ROUND(AVG(CASE WHEN penanganan.status = 'selesai' THEN TIMESTAMPDIFF(HOUR, penanganan.created_at, penanganan.updated_at) END), 1) as rata_jam_selesai
            ")
            ->first();

        $totalPenanganan = (int) ($summary->total ?? 0);
        $selesai = (int) ($summary->selesai ?? 0);
        $menungguRespon = (int) ($summary->menunggu_respon ?? 0);
        $menungguTindakLanjut = (int) ($summary->menunggu_tindak_lanjut ?? 0);
        $ratingAvg = (float) ($summary->rating_avg ?? 0);
        $totalDinilai = (int) ($summary->total_dinilai ?? 0);
        $rataJamSelesai = (float) ($summary->rata_jam_selesai ?? 0);
        $completionRate = round(($selesai / max($totalPenanganan, 1)) * 100, 1);

        $statusBreakdown = [
            'selesai' => $selesai,
            'menunggu_respon' => $menungguRespon,
            'menunggu_tindak_lanjut' => $menungguTindakLanjut,
        ];

        $hasilBreakdown = (clone $baseQuery)
            ->where('penanganan.status', 'selesai')
            ->selectRaw("COALESCE(penanganan.hasil, 'tanpa_hasil') as hasil, COUNT(*) as total")
            ->groupByRaw("COALESCE(penanganan.hasil, 'tanpa_hasil')")
            ->orderByDesc('total')
            ->pluck('total', 'hasil');

        $petugasPerformance = (clone $baseQuery)
            ->join('users', 'users.id', '=', 'penanganan.id_petugas')
            ->select([
                'users.id',
                'users.name',
                'users.lembaga',
                DB::raw('COUNT(penanganan.id) as total'),
                DB::raw("COUNT(CASE WHEN penanganan.status = 'selesai' THEN 1 END) as selesai"),
                DB::raw("COUNT(CASE WHEN penanganan.status != 'selesai' THEN 1 END) as aktif"),
            ])
            ->groupBy('users.id', 'users.name', 'users.lembaga')
            ->orderByDesc('total')
            ->orderByDesc('selesai')
            ->limit(8)
            ->get()
            ->map(function ($item) use ($totalPenanganan) {
                $item->completion_rate = round(($item->selesai / max($item->total, 1)) * 100, 1);
                $item->activity_share = round(($item->total / max($totalPenanganan, 1)) * 100, 1);
                return $item;
            });

        $petugasDetailIds = $petugasPerformance->pluck('id');
        $penangananPetugas = $petugasDetailIds->isEmpty()
            ? collect()
            : (clone $baseQuery)
                ->whereIn('penanganan.id_petugas', $petugasDetailIds)
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
            'menungguRespon',
            'menungguTindakLanjut',
            'ratingAvg',
            'totalDinilai',
            'rataJamSelesai',
            'completionRate',
            'statusBreakdown',
            'hasilBreakdown',
            'petugasPerformance',
            'penangananPetugas'
        ));
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
