<?php

namespace App\Jobs;

use App\Services\SiswaService;
use App\Models\Siswa;
use App\Models\SiswaPembayaran;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\SiswaSaldo;

class SyncPembayaranSiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idperson;

    public function __construct($idperson)
    {
        $this->idperson = $idperson;
    }

    public function queue(): string
    {
        return 'sync-pembayaran';
    }

    /**
     * Handle job dengan error handling yang proper
     */
    public function handle(SiswaService $service)
    {
        try {
            // ambil data pembayaran via API
            $result = $service->getPembayaranSiswa($this->idperson);

            if ($result['status']) {

                $siswa = Siswa::where('idperson', $this->idperson)->first();

                if (!$siswa) {
                    return;
                }

                // 1. Ambil data utama (biasanya di index 0)
                $apiDataSiswa = $result['data'][0] ?? null;

                if ($apiDataSiswa) {
                    // --- PROSES UPDATE SALDO (TAMBAHAN) ---
                    if (isset($apiDataSiswa['saldo'])) {
                        SiswaSaldo::updateOrCreate(
                            ['siswa_id' => $siswa->id],
                            ['saldo' => (float) $apiDataSiswa['saldo']]
                        );
                    }

                    // --- PROSES UPDATE PEMBAYARAN PER PERIODE ---
                    $periods = $apiDataSiswa['periods'] ?? [];
                    foreach ($periods as $period) {
                        SiswaPembayaran::updateOrCreate(
                            [
                                'siswa_id' => $siswa->id,
                                'periode' => $period['period_id'],
                            ],
                            [
                                'data' => $period,
                            ]
                        );
                    }
                }

                Log::info('SyncPembayaranSiswaJob success for ' . $this->idperson);
            } else {
                // Jika gagal, increment failed counter
                Cache::increment('sync_pembayaran_failed');
                Log::warning('SyncPembayaranSiswaJob failed (API error) for ' . $this->idperson, [
                    'message' => $result['message'] ?? 'Unknown error'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('SyncPembayaranSiswaJob exception for ' . $this->idperson, [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            Cache::increment('sync_pembayaran_failed');

            // Jika sudah attempt ke-3, jangan retry lagi
            if ($this->attempts() >= 3) {
                Log::error('SyncPembayaranSiswaJob FAILED after 3 attempts for ' . $this->idperson);
                // Jangan throw exception, biar job selesai dengan graceful
                Cache::increment('sync_pembayaran_processed');
                return;
            }

            // Throw exception untuk trigger retry oleh Laravel
            throw $e;
        }

        // Tambah progress
        Cache::increment('sync_pembayaran_processed');
    }
}
