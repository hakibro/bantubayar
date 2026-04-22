<?php

namespace App\Jobs;

use App\Services\SiswaService;
use App\Models\Siswa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;

class SyncPembayaranSummarySiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $siswaId;
    public $tries = 3;

    public function __construct($siswaId)
    {
        $this->siswaId = $siswaId;
    }

    public function queue(): string
    {
        return 'sync-pembayaran';
    }

    public function handle(SiswaService $siswaService)
    {
        if ($this->batch() && $this->batch()->cancelled()) {
            return;
        }

        try {
            $result = $siswaService->syncPembayaranSummarySiswa($this->siswaId);
            if (!$result['status']) {
                throw new \Exception($result['message'] ?? 'Sync summary gagal');
            }
            Log::info("Sync summary success for siswa_id {$this->siswaId}");
        } catch (\Exception $e) {
            Log::error("Sync summary error for siswa_id {$this->siswaId}", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}