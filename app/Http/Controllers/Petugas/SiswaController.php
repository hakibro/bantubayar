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
     * Memulai sinkronisasi summary semua siswa (berdasarkan scope user)
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

            // 🔥 SIMPAN PROGRESS AWAL (status pending)
            Cache::put($progressKey, [
                'total' => count($siswaIds),
                'processed' => 0,
                'failed' => 0,
                'status' => 'pending',
            ], now()->addHours(1));

            \Log::info('SyncAllSummary - Dispatch Job', [
                'user_id' => $user->id,
                'siswa_count' => count($siswaIds),
                'progress_key' => $progressKey
            ]);

            // Use Queue::push directly untuk memastikan job ter-queue dengan reliable
            \Illuminate\Support\Facades\Queue::push(
                new SyncPembayaranSummaryAllJob($siswaIds, $progressKey)
            );

            return response()->json(['success' => true, 'progress_key' => $progressKey]);

        } catch (\Exception $e) {
            \Log::error('SyncAllSummary Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan sinkronisasi summary yang sedang berjalan
     */
    public function cancelSyncSummary(Request $request)
    {
        $progressKey = $request->input('progress_key');
        if (!$progressKey) {
            return response()->json(['success' => false, 'message' => 'Progress key tidak ditemukan.']);
        }

        // 1. Set flag pembatalan untuk job yang sedang berjalan
        \App\Jobs\SyncPembayaranSummaryAllJob::markAsCancelled($progressKey);

        // 2. Hapus job yang masih pending di queue (belum diproses)
        //    Cari job dengan payload yang mengandung progressKey ini
        $deleted = \DB::table('jobs')
            ->where('queue', 'sync-pembayaran')
            ->where('payload', 'like', '%"progressKey":"' . $progressKey . '"%')
            ->delete();

        // 3. Hapus cache progress (opsional, biar tidak muncul lagi)
        Cache::forget($progressKey);

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
        $progress = Cache::get($progressKey);

        if (!$progress) {
            return response()->json(['success' => false, 'message' => 'Progress tidak ditemukan.'], 404);
        }

        $percentage = $progress['total'] > 0 ? round(($progress['processed'] / $progress['total']) * 100) : 0;

        return response()->json([
            'success' => true,
            'total' => $progress['total'],
            'processed' => $progress['processed'],
            'failed' => $progress['failed'],
            'percentage' => $percentage,
            'status' => $progress['status'],
        ]);
    }

}
