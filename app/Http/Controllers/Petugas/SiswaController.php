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
use App\Jobs\SyncPembayaranSummarySiswaJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;

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
    function syncAllSummary(Request $request)
    {
        try {

            $user = auth()->user();
            $userCacheKey = 'sync_batch_user_' . $user->id;  // ✅ key per user

            // Cek apakah user ini sudah punya batch aktif
            $existingBatchId = Cache::get($userCacheKey);
            if ($existingBatchId) {
                $existingBatch = Bus::findBatch($existingBatchId);
                if ($existingBatch && !$existingBatch->finished()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah memiliki sinkronisasi yang sedang berjalan.',
                        'batch_id' => $existingBatchId,
                    ]);
                }
            }

            $scope = Siswa::query();
            if ($user->hasRole('petugas')) {
                $scope->whereHas('petugas', fn($q) => $q->where('users.id', $user->id));
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

            // ✅ HAPUS: Tidak lagi delete semua job di queue (bisa milik user lain!)
            // \DB::table('jobs')->where('queue', 'sync-pembayaran')->delete();

            $jobs = collect($siswaIds)->map(fn($id) => new SyncPembayaranSummarySiswaJob($id))->all();

            $batch = Bus::batch($jobs)
                ->onQueue('sync-pembayaran')
                ->onConnection('database')
                ->before(function (Batch $batch) use ($user, $userCacheKey) {
                    Log::info('Sync summary batch started', ['batch_id' => $batch->id, 'user_id' => $user->id]);
                })
                ->progress(function (Batch $batch) {
                    Cache::put('sync_summary_batch_' . $batch->id, [
                        'processed' => $batch->processedJobs(),
                        'failed' => $batch->failedJobs,
                        'total' => $batch->totalJobs,
                    ], now()->addHours(1));
                })
                ->then(function (Batch $batch) use ($userCacheKey, $user) {
                    Cache::forget($userCacheKey);
                    // ✅ Hapus dari daftar global
                    $all = Cache::get('sync_all_active_batches', []);
                    unset($all[$user->id]);
                    Cache::put('sync_all_active_batches', $all, now()->addHours(2));
                })
                ->catch(function (Batch $batch, \Throwable $e) use ($userCacheKey, $user) {
                    Cache::forget($userCacheKey);
                    // ✅ Hapus dari daftar global
                    $all = Cache::get('sync_all_active_batches', []);
                    unset($all[$user->id]);
                    Cache::put('sync_all_active_batches', $all, now()->addHours(2));
                })
                ->dispatch();

            // ✅ Simpan batch_id per user di server-side Cache
            Cache::put($userCacheKey, $batch->id, now()->addHours(2));

            $allActiveBatches = Cache::get('sync_all_active_batches', []);
            $allActiveBatches[$user->id] = [
                'batch_id' => $batch->id,
                'started_at' => now()->timestamp,
                'user_name' => $user->name,
            ];
            Cache::put('sync_all_active_batches', $allActiveBatches, now()->addHours(2));

            return response()->json([
                'success' => true,
                'message' => 'Proses sinkronisasi summary dimulai.',
                'batch_id' => $batch->id,
            ]);

        } catch (\Exception $e) {
            Log::error('SyncAllSummary Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function checkOtherActiveSync()
    {
        $currentUserId = auth()->id();

        // Cari semua cache key batch user lain yang sedang aktif
        // Kita simpan daftar active batch di cache global
        $allActiveBatches = Cache::get('sync_all_active_batches', []);

        foreach ($allActiveBatches as $userId => $batchInfo) {
            if ($userId == $currentUserId)
                continue;

            $batch = Bus::findBatch($batchInfo['batch_id']);
            if ($batch && !$batch->finished()) {
                return response()->json([
                    'success' => true,
                    'has_other' => true,
                    'started_at' => $batchInfo['started_at'], // unix timestamp
                    'user_name' => $batchInfo['user_name'],
                    'total' => $batch->totalJobs,
                    'processed' => $batch->processedJobs(),
                ]);
            }

            // Batch sudah selesai, hapus dari daftar
            unset($allActiveBatches[$userId]);
            Cache::put('sync_all_active_batches', $allActiveBatches, now()->addHours(2));
        }

        return response()->json(['success' => true, 'has_other' => false]);
    }

    public function getActiveBatch()
    {
        $userCacheKey = 'sync_batch_user_' . auth()->id();
        $batchId = Cache::get($userCacheKey);

        if (!$batchId) {
            return response()->json(['success' => false, 'batch_id' => null]);
        }

        $batch = Bus::findBatch($batchId);
        if (!$batch || $batch->finished()) {
            Cache::forget($userCacheKey);
            return response()->json(['success' => false, 'batch_id' => null]);
        }

        return response()->json(['success' => true, 'batch_id' => $batchId]);
    }

    public function cancelSyncSummary(Request $request)
    {
        $batchId = $request->input('batch_id');
        if (!$batchId) {
            return response()->json(['success' => false, 'message' => 'Batch ID tidak ditemukan.']);
        }

        $batch = Bus::findBatch($batchId);
        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan.']);
        }

        if (!$batch->finished()) {
            $batch->cancel();
        }

        // ✅ Hapus job di queue yang memiliki batch_id ini
        // Job Laravel batch menyimpan payload JSON yang mengandung batchId
        \DB::table('jobs')
            ->where('queue', 'sync-pembayaran')
            ->get()
            ->each(function ($job) use ($batchId) {
                $payload = json_decode($job->payload, true);
                $jobBatchId = $payload['data']['batchId'] ?? null;
                if ($jobBatchId === $batchId) {
                    \DB::table('jobs')->where('id', $job->id)->delete();
                }
            });

        // ✅ Bersihkan cache user & global
        $user = auth()->user();
        $userCacheKey = 'sync_batch_user_' . $user->id;
        Cache::forget($userCacheKey);

        $all = Cache::get('sync_all_active_batches', []);
        unset($all[$user->id]);
        Cache::put('sync_all_active_batches', $all, now()->addHours(2));

        return response()->json(['success' => true, 'message' => 'Sinkronisasi dibatalkan.']);
    }
    public function getSyncSummaryProgress($batchId)
    {
        $batch = Bus::findBatch($batchId);
        if (!$batch) {
            return response()->json(['success' => false, 'message' => 'Batch tidak ditemukan.'], 404);
        }


        // Hitung percentage secara manual
        $percentage = $batch->totalJobs > 0
            ? round(($batch->processedJobs() / $batch->totalJobs) * 100, 1)
            : 0;

        return response()->json([
            'success' => true,
            'total' => $batch->totalJobs,
            'processed' => $batch->processedJobs(),
            'failed' => $batch->failedJobs,
            'pending' => $batch->pendingJobs,
            'percentage' => $percentage,
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
        ]);
    }

}
