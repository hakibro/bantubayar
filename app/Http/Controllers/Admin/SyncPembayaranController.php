<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPembayaranSiswaJob;
use App\Models\Siswa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Bus;
use Illuminate\Http\Request;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\DB;
use Throwable;

class SyncPembayaranController extends Controller
{
    /**
     * Tampilkan halaman sinkronisasi pembayaran
     */
    public function index()
    {
        $totalSiswa = Siswa::count();

        // Ambil informasi batch terakhir dari cache (jika ada)
        $batchId = Cache::get('sync_pembayaran_batch_id');
        $isRunning = false;
        $processedSiswa = 0;
        $failedSiswa = 0;
        $totalSiswaSync = 0;

        if ($batchId) {
            $batch = Bus::findBatch($batchId);
            if ($batch && !$batch->finished()) {
                $isRunning = true;
                $processedSiswa = $batch->processedJobs();
                $failedSiswa = $batch->failedJobs;
                $totalSiswaSync = $batch->totalJobs;
            } else {
                // Batch sudah selesai, hapus cache
                Cache::forget('sync_pembayaran_batch_id');
                Cache::forget('sync_pembayaran_status');
            }
        }

        return view('admin.sync-pembayaran.index', compact(
            'totalSiswa',
            'processedSiswa',
            'failedSiswa',
            'totalSiswaSync',
            'isRunning'
        ));
    }

    /**
     * Mulai proses sinkronisasi menggunakan Batch
     */
    public function start()
    {
        try {
            // Cek apakah sudah ada batch yang berjalan
            $batchId = Cache::get('sync_pembayaran_batch_id');
            if ($batchId) {
                $existingBatch = Bus::findBatch($batchId);
                if ($existingBatch && !$existingBatch->finished()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Proses sinkronisasi sudah berjalan.'
                    ], 409);
                }
            }

            // Hapus job lama yang mungkin tersisa (queue sync-pembayaran)
            DB::table('jobs')->where('queue', 'sync-pembayaran')->delete();

            $total = Siswa::count();
            if ($total === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada siswa di database.'
                ], 400);
            }

            // Kumpulkan semua job
            $jobs = [];
            Siswa::select('idperson')->chunk(500, function ($chunk) use (&$jobs) {
                foreach ($chunk as $siswa) {
                    $jobs[] = new SyncPembayaranSiswaJob($siswa->idperson);
                }
            });

            // Buat batch dengan callback untuk update cache status
            $batch = Bus::batch($jobs)
                ->onQueue('sync-pembayaran')
                ->onConnection('database')
                ->before(function (Batch $batch) {
                    Cache::put('sync_pembayaran_status', 'running', now()->addHours(1));
                    Cache::put('sync_pembayaran_batch_id', $batch->id, now()->addHours(1));
                })
                ->progress(function (Batch $batch) {
                    // Update cache progress setiap kali ada perubahan (opsional)
                    // Bisa juga tidak perlu karena frontend akan polling ke endpoint progress
                })
                ->then(function (Batch $batch) {
                    // Semua job sukses
                    Cache::put('sync_pembayaran_status', 'completed', now()->addHours(1));
                    Log::info('SyncPembayaran batch completed', [
                        'total' => $batch->totalJobs,
                        'processed' => $batch->processedJobs(),
                        'failed' => $batch->failedJobs
                    ]);
                })
                ->catch(function (Batch $batch, Throwable $e) {
                    // Ada job yang gagal (setelah retry habis)
                    Cache::put('sync_pembayaran_status', 'failed', now()->addHours(1));
                    Log::error('SyncPembayaran batch failed', [
                        'error' => $e->getMessage()
                    ]);
                })
                ->finally(function (Batch $batch) {
                    // Hapus cache batch ID setelah selesai
                    Cache::forget('sync_pembayaran_batch_id');
                })
                ->dispatch();

            Log::info('SyncPembayaran batch started', ['batch_id' => $batch->id, 'total' => $total]);

            return response()->json([
                'status' => true,
                'message' => 'Proses sinkronisasi pembayaran dimulai dengan batch.',
                'batch_id' => $batch->id
            ]);
        } catch (\Exception $e) {
            Cache::forget('sync_pembayaran_status');
            Cache::forget('sync_pembayaran_batch_id');
            Log::error('SyncPembayaran start error', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan batch yang sedang berjalan
     */
    public function cancel()
    {
        try {
            $batchId = Cache::get('sync_pembayaran_batch_id');
            if ($batchId) {
                $batch = Bus::findBatch($batchId);
                if ($batch && !$batch->finished()) {
                    $batch->cancel(); // Membatalkan semua job yang belum diproses
                    Log::info('Batch cancelled', ['batch_id' => $batchId]);
                }
            }

            // Hapus semua job yang masih tertunda di queue (backup)
            DB::table('jobs')->where('queue', 'sync-pembayaran')->delete();

            // Reset cache
            Cache::forget('sync_pembayaran_status');
            Cache::forget('sync_pembayaran_batch_id');
            Cache::forget('sync_pembayaran_total');
            Cache::forget('sync_pembayaran_processed');
            Cache::forget('sync_pembayaran_failed');

            return response()->json([
                'status' => true,
                'message' => 'Proses sinkronisasi dibatalkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil progress dari batch (digunakan polling frontend)
     */
    public function progress()
    {
        try {
            $batchId = Cache::get('sync_pembayaran_batch_id');
            if (!$batchId) {
                // Tidak ada batch aktif
                return response()->json([
                    'total' => 0,
                    'processed' => 0,
                    'failed' => 0,
                    'percent' => 0,
                    'isRunning' => false,
                    'successCount' => 0
                ]);
            }

            $batch = Bus::findBatch($batchId);
            if (!$batch) {
                // Batch tidak ditemukan
                Cache::forget('sync_pembayaran_batch_id');
                return response()->json([
                    'total' => 0,
                    'processed' => 0,
                    'failed' => 0,
                    'percent' => 0,
                    'isRunning' => false,
                    'successCount' => 0
                ]);
            }

            $total = $batch->totalJobs;
            $processed = $batch->processedJobs();
            $failed = $batch->failedJobs;
            $isRunning = !$batch->finished();
            $percent = $total > 0 ? round(($processed / $total) * 100, 2) : 0;

            return response()->json([
                'total' => $total,
                'processed' => $processed,
                'failed' => $failed,
                'percent' => $percent,
                'isRunning' => $isRunning,
                'successCount' => $processed - $failed
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset/clear progress (hapus cache dan batch yang mungkin stuck)
     */
    public function reset()
    {
        try {
            $batchId = Cache::get('sync_pembayaran_batch_id');
            if ($batchId) {
                $batch = Bus::findBatch($batchId);
                if ($batch && !$batch->finished()) {
                    $batch->cancel(); // batalkan jika masih running
                }
            }
            Cache::forget('sync_pembayaran_status');
            Cache::forget('sync_pembayaran_batch_id');
            Cache::forget('sync_pembayaran_total');
            Cache::forget('sync_pembayaran_processed');
            Cache::forget('sync_pembayaran_failed');
            // Hapus juga job yang tersisa
            DB::table('jobs')->where('queue', 'sync-pembayaran')->delete();

            return response()->json([
                'status' => true,
                'message' => 'Progress di-reset.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}