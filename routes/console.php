<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('pembayaran:refresh-status-lunas', function () {
    $this->info('Menghitung ulang status pembayaran siswa...');

    DB::statement("
        CREATE TEMPORARY TABLE tmp_siswa_status_pembayaran AS
        SELECT
            summary.idperson,
            summary.total_tunggakan,
            CASE WHEN summary.total_tunggakan > 0 THEN 0 ELSE 1 END AS is_lunas,
            NOW() AS refreshed_at
        FROM (
            SELECT
                iis.idperson,
                COALESCE(SUM(
                    CASE
                        WHEN (iis.jml_kredit - iis.jml_debet) > 0
                            THEN iis.jml_kredit - iis.jml_debet
                        ELSE 0
                    END
                ), 0) AS total_tunggakan
            FROM daruttaqwa_trans.ips_siswa iis
            WHERE iis.idperiode IN ('20212022', '20222023', '20232024', '20242025', '20252026')
              AND iis.status = '1'
              AND iis.tgl_jurnal < NOW()
            GROUP BY iis.idperson
        ) summary
    ");

    DB::transaction(function () {
        DB::table('siswa_status_pembayaran')->delete();
        DB::statement("
            INSERT INTO siswa_status_pembayaran (idperson, total_tunggakan, is_lunas, refreshed_at)
            SELECT idperson, total_tunggakan, is_lunas, refreshed_at
            FROM tmp_siswa_status_pembayaran
        ");
    });

    $total = DB::table('siswa_status_pembayaran')->count();
    $this->info("Status pembayaran {$total} siswa berhasil diperbarui.");
})->purpose('Refresh cache status lunas dan total tunggakan siswa');

Schedule::command('pembayaran:refresh-status-lunas')
    ->everyFiveMinutes()
    ->withoutOverlapping();
