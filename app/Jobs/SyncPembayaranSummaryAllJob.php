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

class SyncPembayaranSummaryAllJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $siswaIds;
    protected $progressKey;

    public $timeout = 3600;
    public $tries = 1;

    public function __construct(array $siswaIds, string $progressKey)
    {
        $this->siswaIds = $siswaIds;
        $this->progressKey = $progressKey;
    }

    public function queue(): string
    {
        return 'sync-pembayaran';
    }

    /**
     * Cek apakah job ini dibatalkan
     */
    protected function isCancelled(): bool
    {
        return Cache::get("sync_summary_cancel_{$this->progressKey}", false);
    }

    /**
     * Tandai job sebagai dibatalkan (dipanggil dari controller)
     */
    public static function markAsCancelled(string $progressKey): void
    {
        Cache::put("sync_summary_cancel_{$progressKey}", true, now()->addHours(1));
    }

    public function handle(SiswaService $siswaService)
    {
        $total = count($this->siswaIds);
        $processed = 0;
        $failed = 0;

        Log::info("SyncPembayaranSummaryAllJob START", [
            'progress_key' => $this->progressKey,
            'total' => $total
        ]);

        // Update status menjadi processing
        Cache::put($this->progressKey, [
            'total' => $total,
            'processed' => 0,
            'failed' => 0,
            'status' => 'processing',
        ], now()->addHours(1));

        foreach ($this->siswaIds as $index => $siswaId) {
            // Cek pembatalan sebelum memproses setiap siswa
            if ($this->isCancelled()) {
                Log::warning("Job dibatalkan oleh user", [
                    'progress_key' => $this->progressKey,
                    'processed' => $processed,
                    'failed' => $failed
                ]);
                // Update status menjadi cancelled
                Cache::put($this->progressKey, [
                    'total' => $total,
                    'processed' => $processed,
                    'failed' => $failed,
                    'status' => 'cancelled',
                ], now()->addHours(1));
                return;
            }

            try {
                $result = $siswaService->syncPembayaranSummarySiswa($siswaId);
                if (!$result['status']) {
                    $failed++;
                }
            } catch (\Exception $e) {
                Log::error("Error sync siswa {$siswaId}", ['error' => $e->getMessage()]);
                $failed++;
            }

            $processed++;
            // Update progress setiap 10 siswa atau setelah selesai
            if ($processed % 10 === 0 || $processed === $total) {
                Cache::put($this->progressKey, [
                    'total' => $total,
                    'processed' => $processed,
                    'failed' => $failed,
                    'status' => 'processing',
                ], now()->addHours(1));
            }
        }

        // Selesai normal
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

        // Hapus flag pembatalan jika ada
        Cache::forget("sync_summary_cancel_{$this->progressKey}");
    }
}