<?php

namespace App\Jobs;

use App\Services\PembayaranService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class RefreshStatusPembayaranJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const CACHE_KEY = 'sync_status_pembayaran';

    public function handle(PembayaranService $service): void
    {
        Cache::put(self::CACHE_KEY, [
            'running' => true,
            'started_at' => now()->toDateTimeString(),
        ], now()->addDay());

        try {
            $total = $service->refreshStatusLunasSemuaSiswa();

            Cache::put(self::CACHE_KEY, [
                'running' => false,
                'last_finished_at' => now()->toDateTimeString(),
                'last_total' => $total,
                'last_error' => null,
            ], now()->addDay());
        } catch (\Throwable $e) {
            Cache::put(self::CACHE_KEY, [
                'running' => false,
                'last_error' => $e->getMessage(),
            ], now()->addDay());

            throw $e;
        }
    }
}
