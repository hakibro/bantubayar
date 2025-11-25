<?php

namespace App\Jobs;

use App\Models\Siswa;
use App\Jobs\SyncPembayaranSiswaJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DispatchSyncPembayaranJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Hitung total siswa
        $total = Siswa::count();

        if ($total === 0) {
            return;
        }

        // Simpan total & reset progress
        cache()->put('sync_pembayaran_total', $total);
        cache()->put('sync_pembayaran_processed', 0);
        cache()->put('sync_pembayaran_failed', 0);

        // Dispatch job per siswa
        Siswa::select('id', 'idperson')->chunk(100, function ($chunk) {
            foreach ($chunk as $siswa) {
                // Pass idperson string, bukan object siswa
                SyncPembayaranSiswaJob::dispatch($siswa->idperson);
            }
        });
    }
}
