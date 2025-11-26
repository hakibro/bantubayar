<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncPembayaranSiswaJob;
use App\Models\Siswa;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Http\Request;

class SyncPembayaranController extends Controller
{
    /**
     * Tampilkan halaman sinkronisasi pembayaran
     */
    public function index()
    {
        $totalSiswa = Siswa::count();
        $processedSiswa = Cache::get('sync_pembayaran_processed', 0);
        $failedSiswa = Cache::get('sync_pembayaran_failed', 0);
        $totalSiswaSync = Cache::get('sync_pembayaran_total', 0);
        $isRunning = Cache::has('sync_pembayaran_status') && Cache::get('sync_pembayaran_status') === 'running';

        return view('admin.sync-pembayaran.index', compact(
            'totalSiswa',
            'processedSiswa',
            'failedSiswa',
            'totalSiswaSync',
            'isRunning'
        ));
    }

    /**
     * Mulai proses sinkronisasi pembayaran
     */
    public function start()
    {
        try {
            // Cek apakah proses sudah running
            if (Cache::get('sync_pembayaran_status') === 'running') {
                return response()->json([
                    'status' => false,
                    'message' => 'Proses sinkronisasi sudah berjalan.'
                ], 409);
            }

            // Hitung total siswa
            $total = Siswa::count();

            if ($total === 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada siswa di database.'
                ], 400);
            }

            // Set status sebagai running
            Cache::put('sync_pembayaran_status', 'running', now()->addHours(1));

            // Simpan total & reset progress
            Cache::put('sync_pembayaran_total', $total);
            Cache::put('sync_pembayaran_processed', 0);
            Cache::put('sync_pembayaran_failed', 0);

            // Dispatch job per siswa (direct dispatch, bukan via DispatchSyncPembayaranJob)
            Siswa::select('id', 'idperson')->chunk(100, function ($chunk) {
                foreach ($chunk as $siswa) {
                    try {
                        $job = (new SyncPembayaranSiswaJob($siswa->idperson))->onQueue('sync-pembayaran');
                        Queue::connection('database')->push($job);
                    } catch (\Exception $e) {
                        Log::error('Error dispatching job for ' . $siswa->idperson, [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            Log::info('SyncPembayaran started', [
                'total' => $total
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Proses sinkronisasi pembayaran dimulai.'
            ]);
        } catch (\Exception $e) {
            Cache::forget('sync_pembayaran_status');
            Log::error('SyncPembayaran start error', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Batalkan proses sinkronisasi pembayaran
     */
    public function cancel()
    {
        try {
            // Clear semua cache sync
            Cache::forget('sync_pembayaran_status');
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
     * Ambil progress sinkronisasi pembayaran
     */
    public function progress()
    {
        try {
            $total = Cache::get('sync_pembayaran_total', 0);
            $processed = Cache::get('sync_pembayaran_processed', 0);
            $failed = Cache::get('sync_pembayaran_failed', 0);
            $isRunning = Cache::get('sync_pembayaran_status') === 'running';

            $percent = $total > 0 ? round(($processed / $total) * 100, 2) : 0;

            // Jika sudah selesai (processed == total dan running)
            if ($processed >= $total && $total > 0 && $isRunning) {
                Cache::put('sync_pembayaran_status', 'completed', now()->addHours(1));
            }

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
     * Reset/clear progress
     */
    public function reset()
    {
        try {
            Cache::forget('sync_pembayaran_status');
            Cache::forget('sync_pembayaran_total');
            Cache::forget('sync_pembayaran_processed');
            Cache::forget('sync_pembayaran_failed');

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
