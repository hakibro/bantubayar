<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Services\SiswaService;
use App\Models\User;
use App\Models\Siswa;
use App\Models\PetugasSiswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SyncPembayaranSummaryAllJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SyncPembayaranSummaryChunkJob; // tambahkan ini

use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $lembagaUser = auth()->user()->lembaga;
        $scope = Siswa::query();

        // 1) Scope berdasarkan Role
        if (Auth::user()->hasRole('petugas')) {
            $scope->whereHas('petugas', function ($q) {
                $q->where('users.id', Auth::id());
            });
        } else {
            $scope->where(function ($q) use ($lembagaUser) {
                $q->where('UnitFormal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatDiniyah', $lembagaUser);
            });
        }

        $filterOptions = [];
        // Kolom tabel siswa untuk dropdown
        $siswaCols = ['UnitFormal', 'KelasFormal', 'AsramaPondok', 'KamarPondok', 'TingkatDiniyah', 'KelasDiniyah'];

        // 2) Ambil Options (Hanya saat bukan request AJAX)
        if (!$request->ajax()) {
            foreach ($siswaCols as $col) {
                $filterOptions[$col] = (clone $scope)
                    ->select($col)
                    ->whereNotNull($col)
                    ->distinct()
                    ->orderBy($col)
                    ->pluck($col);
            }
            // Ambil Enum dari tabel 'penanganan'
            $filterOptions['status_penanganan'] = $this->getEnumValues('penanganan', 'status');
        }

        // 3) Logika Penguncian Lembaga
        $lock = ['UnitFormal' => false, 'AsramaPondok' => false, 'TingkatDiniyah' => false];
        $selected = ['UnitFormal' => null, 'AsramaPondok' => null, 'TingkatDiniyah' => null];

        if (!$request->ajax()) {
            foreach (['UnitFormal', 'AsramaPondok', 'TingkatDiniyah'] as $f) {
                if (isset($filterOptions[$f]) && in_array($lembagaUser, $filterOptions[$f]->toArray())) {
                    $lock[$f] = true;
                    $selected[$f] = $lembagaUser;
                }
            }
        }

        // 4) Build Query Utama
        $query = (clone $scope);

        // PENTING: Merge selected (lembaga yang dikunci) dengan request filter lainnya
        $allFilters = array_merge($selected, $request->only(array_merge($siswaCols, ['status_penanganan', 'pembayaran_status'])));

        // Apply Filter Siswa
        foreach ($siswaCols as $field) {
            $val = $request->get($field, $selected[$field] ?? null);
            if ($val) {
                $query->where($field, $val);
            }
        }

        $now = Carbon::now();

        // Filter Penanganan
        if ($request->status_penanganan) {
            if ($request->status_penanganan === 'belum_ditangani') {
                $query->whereDoesntHave('penanganan', function ($q) use ($now) {
                    $q->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                });
            } else {
                $query->whereHas('penanganan', function ($q) use ($request) {
                    $q->where('status', $request->status_penanganan);
                });
            }
        }

        // Filter Pembayaran (Menggunakan kolom is_lunas yang baru)
        if ($request->pembayaran_status) {
            $isLunas = $request->pembayaran_status === 'lunas' ? 1 : 0;
            $query->where('is_lunas', $isLunas);
        }

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        $siswa = $query->paginate(40)->appends($request->query());

        if ($request->ajax()) {
            // Kembalikan partial list DAN link pagination baru
            return response()->json([
                'html' => view('petugas.siswa.partials.list-siswa', compact('siswa'))->render(),
                'pagination' => $siswa->links()->render()
            ]);
        }

        return view('petugas.siswa.index', compact('siswa', 'filterOptions', 'lock', 'selected'));
    }

    public function show($id)
    {
        $siswa = Siswa::with([
            'pembayaran' => function ($q) {
                $q->orderBy('periode', 'desc');
            }
        ])->findOrFail($id);

        return view('petugas.siswa.show', compact('siswa'));
    }

    private function getEnumValues($table, $column)
    {
        // Hapus DB::raw, gunakan string langsung
        $results = \DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);

        if (empty($results))
            return [];

        $type = $results[0]->Type;

        // Mengekstrak nilai di dalam tanda petik
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $values = [];
        if (isset($matches[1])) {
            foreach (explode(',', $matches[1]) as $value) {
                $values[] = trim($value, "'");
            }
        }
        return $values;
    }

    /**
     * Memulai sinkronisasi summary semua siswa (berdasarkan scope user) - dipecah per chunk
     */
    public function syncAllSummary(Request $request)
    {
        try {
            $user = auth()->user();
            $scope = Siswa::query();

            if ($user->hasRole('petugas')) {
                $scope->whereHas('petugas', fn($q) => $q->where('users.id', auth()->id()));
            } else {
                $lembagaUser = $user->lembaga;
                $scope->where(fn($q) => $q->where('UnitFormal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatDiniyah', $lembagaUser));
            }

            $siswaIds = $scope->pluck('id')->toArray();

            if (empty($siswaIds)) {
                return response()->json(['success' => false, 'message' => 'Tidak ada siswa dalam lingkup Anda.']);
            }

            $progressKey = 'sync_summary_' . $user->id . '_' . Str::random(8);
            $total = count($siswaIds);

            // Simpan metadata progress
            Cache::put($progressKey . '_total', $total, now()->addHours(1));
            Cache::put($progressKey . '_processed', 0, now()->addHours(1));
            Cache::put($progressKey . '_failed', 0, now()->addHours(1));
            Cache::put($progressKey . '_status', 'processing', now()->addHours(1));

            // Bagi siswa menjadi chunk (misal 10 siswa per job)
            $chunkSize = 10;
            $chunks = array_chunk($siswaIds, $chunkSize);

            foreach ($chunks as $chunk) {
                SyncPembayaranSummaryChunkJob::dispatch($chunk, $progressKey, $total);
            }
            Log::info('SyncAllSummary - Dispatched chunk jobs', [
                'user_id' => $user->id,
                'total_siswa' => $total,
                'chunks' => count($chunks),
                'progress_key' => $progressKey
            ]);

            return response()->json(['success' => true, 'progress_key' => $progressKey]);

        } catch (\Exception $e) {
            Log::error('SyncAllSummary Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelSyncSummary(Request $request)
    {
        $progressKey = $request->input('progress_key');
        if (!$progressKey) {
            return response()->json(['success' => false, 'message' => 'Progress key tidak ditemukan.']);
        }

        // Set flag pembatalan
        Cache::put($progressKey . '_cancel', true, now()->addHours(1));

        // Hapus semua job yang masih pending di queue dengan progress key ini
        $deleted = \DB::table('jobs')
            ->where('queue', 'sync-pembayaran')
            ->where('payload', 'like', '%"progressKey":"' . $progressKey . '"%')
            ->delete();

        // Update status menjadi cancelled
        Cache::put($progressKey . '_status', 'cancelled', now()->addHours(1));

        Log::info("Sync summary dibatalkan oleh user", [
            'progress_key' => $progressKey,
            'deleted_jobs' => $deleted
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi dibatalkan. ' . $deleted . ' job pending dihapus.'
        ]);
    }
    public function getSyncProgress($progressKey)
    {
        $total = Cache::get($progressKey . '_total');
        if ($total === null) {
            return response()->json(['success' => false, 'message' => 'Progress tidak ditemukan.'], 404);
        }

        $processed = Cache::get($progressKey . '_processed', 0);
        $failed = Cache::get($progressKey . '_failed', 0);
        $status = Cache::get($progressKey . '_status', 'processing');

        $percentage = $total > 0 ? round(($processed / $total) * 100) : 0;

        return response()->json([
            'success' => true,
            'total' => $total,
            'processed' => $processed,
            'failed' => $failed,
            'percentage' => $percentage,
            'status' => $status,
        ]);
    }

}
