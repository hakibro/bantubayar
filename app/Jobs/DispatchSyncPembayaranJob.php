<?php

namespace App\Jobs;

use App\Models\Siswa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchSyncPembayaranJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $siswaList = Siswa::select('idperson')->pluck('idperson');

        foreach ($siswaList as $idperson) {
            SyncPembayaranSiswaJob::dispatch($idperson);
        }
    }
}
