<?php

namespace App\Jobs;

use App\Services\SiswaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncPembayaranSummaryChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswaIds;
    protected $progressKey;
    protected $total;

    public $timeout = 600; // 10 menit per chunk
    public $tries = 1;

    public function __construct(array $siswaIds, string $progressKey, int $total)
    {
        $this->siswaIds = $siswaIds;
        $this->progressKey = $progressKey;
        $this->total = $total;
    }

    public function queue(): string
    {
        return 'sync-pembayaran';
    }

    protected function isCancelled(): bool
    {
        return Cache::get($this->progressKey . '_cancel', false);
    }

    public function handle(SiswaService $siswaService)
    {
        if ($this->isCancelled()) {
            Log::info("Chunk job dibatalkan", ['progress_key' => $this->progressKey]);
            return;
        }

        $processedInChunk = 0;
        $failedInChunk = 0;

        foreach ($this->siswaIds as $siswaId) {
            if ($this->isCancelled()) {
                Log::warning("Chunk job berhenti karena pembatalan", [
                    'progress_key' => $this->progressKey,
                    'siswa_id' => $siswaId
                ]);
                break;
            }

            try {
                $result = $siswaService->syncPembayaranSummarySiswa($siswaId);
                if ($result['status']) {
                    $processedInChunk++;
                } else {
                    $failedInChunk++;
                }
            } catch (\Exception $e) {
                Log::error("Error sync siswa {$siswaId}", ['error' => $e->getMessage()]);
                $failedInChunk++;
            }
        }

        // Update global counter secara atomic
        if ($processedInChunk > 0) {
            Cache::increment($this->progressKey . '_processed', $processedInChunk);
        }
        if ($failedInChunk > 0) {
            Cache::increment($this->progressKey . '_failed', $failedInChunk);
        }

        // Cek apakah semua siswa sudah diproses (processed + failed >= total)
        $processed = Cache::get($this->progressKey . '_processed', 0);
        $failed = Cache::get($this->progressKey . '_failed', 0);
        if ($processed + $failed >= $this->total) {
            Cache::put($this->progressKey . '_status', 'completed', now()->addHours(1));
            Log::info("Sync summary selesai", [
                'progress_key' => $this->progressKey,
                'processed' => $processed,
                'failed' => $failed,
                'total' => $this->total
            ]);
            // Hapus flag cancel jika ada
            Cache::forget($this->progressKey . '_cancel');
        }
    }
}