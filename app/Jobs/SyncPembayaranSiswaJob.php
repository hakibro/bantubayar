<?php

namespace App\Jobs;

use App\Services\SiswaService;
use App\Models\Siswa;
use App\Models\SiswaPembayaran;
use App\Models\SiswaSaldo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;

class SyncPembayaranSiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $idperson;

    // Maksimal 3 percobaan
    public $tries = 3;

    public function __construct($idperson)
    {
        $this->idperson = $idperson;
    }

    /**
     * Queue name (optional, karena sudah di-set via batch)
     */
    public function queue(): string
    {
        return 'sync-pembayaran';
    }

    public function handle(SiswaService $service)
    {
        try {
            $result = $service->getPembayaranSiswa($this->idperson);

            if ($result['status']) {
                $siswa = Siswa::where('idperson', $this->idperson)->first();
                if (!$siswa) {
                    Log::warning("Siswa not found for idperson {$this->idperson}");
                    return;
                }

                $apiDataSiswa = $result['data'][0] ?? null;
                if ($apiDataSiswa) {
                    if (isset($apiDataSiswa['saldo'])) {
                        SiswaSaldo::updateOrCreate(
                            ['siswa_id' => $siswa->id],
                            ['saldo' => (float) $apiDataSiswa['saldo']]
                        );
                    }

                    $periods = $apiDataSiswa['periods'] ?? [];
                    foreach ($periods as $period) {
                        SiswaPembayaran::updateOrCreate(
                            [
                                'siswa_id' => $siswa->id,
                                'periode' => $period['period_id'],
                                'kelas_info' => $period['kelas_info']
                            ],
                            ['data' => $period]
                        );
                    }
                }
                Log::info("Sync success for idperson {$this->idperson}");
            } else {
                Log::warning("API error for idperson {$this->idperson}", [
                    'message' => $result['message'] ?? 'Unknown error'
                ]);
                // Throw exception agar job di-retry (jika tries masih tersisa)
                throw new \Exception($result['message'] ?? 'API error');
            }
        } catch (\Exception $e) {
            Log::error("Job exception for idperson {$this->idperson}", [
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);
            throw $e; // Laravel akan menangani retry sesuai $tries
        }
    }
}