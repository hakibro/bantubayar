<?php

namespace App\Jobs;

use App\Services\SiswaService;
use App\Models\Siswa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncPembayaranSiswaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $idperson;

    public function __construct($idperson)
    {
        $this->idperson = $idperson;
        $this->onQueue('sync-pembayaran'); // optional, biar antriannya rapi
    }

    public function handle(SiswaService $service)
    {
        // ambil data pembayaran via API
        $result = $service->getPembayaranSiswa($this->idperson);

        if (!$result['status']) {
            return;
        }

        // simpan ke DB
        Siswa::where('idperson', $this->idperson)
            ->update([
                'pembayaran' => json_encode($result['data']),
                'updated_at' => now()
            ]);
    }
}
