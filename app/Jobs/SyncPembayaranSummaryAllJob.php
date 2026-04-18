<?php
// app/Jobs/SyncPembayaranSummaryAllJob.php

namespace App\Jobs;

use App\Services\SiswaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncPembayaranSummaryAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswaIds;
    protected $progressKey;

    // Timeout untuk job ini (dalam detik) - untuk 1000 siswa dengan API ~1 detik per siswa
    public $timeout = 3600; // 1 jam

    // Retry configuration
    public $tries = 1;

    public function __construct(array $siswaIds, string $progressKey)
    {
        $this->siswaIds = $siswaIds;
        $this->progressKey = $progressKey;
    }

    /**
     * Tambahkan method ini agar sesuai dengan referensi
     * Menjamin job ini masuk ke antrean yang sama dengan SyncPembayaranSiswaJob
     */
    public function queue(): string
    {
        return 'sync-pembayaran';
    }

    public function handle(SiswaService $siswaService)
    {
        $total = count($this->siswaIds);
        $processed = 0;
        $failed = 0;

        Log::info("SyncPembayaranSummaryAllJob START - Total siswa: {$total}", [
            'progress_key' => $this->progressKey,
            'siswa_ids' => count($this->siswaIds)
        ]);

        // Ubah status dari pending menjadi processing
        Cache::put($this->progressKey, [
            'total' => $total,
            'processed' => 0,
            'failed' => 0,
            'status' => 'processing',
        ], now()->addHours(1));

        foreach ($this->siswaIds as $siswaId) {
            try {
                $result = $siswaService->syncPembayaranSummarySiswa($siswaId);
                if (!$result['status']) {
                    // Gunakan increment agar tidak bentrok dengan proses lain
                    $this->updateProgress($this->progressKey, 'failed');
                }
            } catch (\Exception $e) {
                $this->updateProgress($this->progressKey, 'failed');
            }

            $this->updateProgress($this->progressKey, 'processed');
        }

        Cache::put($this->progressKey, [
            'total' => $total,
            'processed' => $processed,
            'failed' => $failed,
            'status' => 'completed',
        ], now()->addHours(1));

        Log::info("SyncPembayaranSummaryAllJob COMPLETED", [
            'progress_key' => $this->progressKey,
            'total' => $total,
            'processed' => $processed,
            'failed' => $failed
        ]);
    }
    private function updateProgress($key, $type)
    {
        $data = Cache::get($key);
        if ($data) {
            $data[$type]++;
            // Update status jika sudah selesai di loop terakhir
            if ($data['processed'] + $data['failed'] >= $data['total']) {
                $data['status'] = 'completed';
            }
            Cache::put($key, $data, now()->addHours(1));
        }
    }
}