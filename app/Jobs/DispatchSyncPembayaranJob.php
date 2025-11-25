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

        // Simpan total & reset progress
        cache()->put('sync_pembayaran_total', $total);
        cache()->put('sync_pembayaran_processed', 0);

        // Dispatch job kecil
        Siswa::select('id', 'idperson')->chunk(100, function ($chunk) {
            foreach ($chunk as $siswa) {
                SyncPembayaranSiswaJob::dispatch($siswa);
            }
        });
    }
}
