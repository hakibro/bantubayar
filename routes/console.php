<?php

use Illuminate\Foundation\Inspiring;
use App\Services\PembayaranService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pembayaran:refresh-status-lunas {idperson?}', function () {
    $service = app(PembayaranService::class);
    $idperson = $this->argument('idperson');

    if ($idperson) {
        $service->refreshStatusLunasSiswa((string) $idperson);
        $this->info("Status pembayaran siswa {$idperson} berhasil diperbarui.");

        return;
    }

    $this->info('Menghitung ulang status pembayaran semua siswa...');

    $total = $service->refreshStatusLunasSemuaSiswa();
    $this->info("Status pembayaran {$total} siswa berhasil diperbarui.");
})->purpose('Refresh cache status lunas dan total tunggakan siswa');

Schedule::command('pembayaran:refresh-status-lunas')
    ->everySixHours()
    ->withoutOverlapping();
