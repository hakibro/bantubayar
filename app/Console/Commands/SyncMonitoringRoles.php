<?php

namespace App\Console\Commands;

use App\Models\LembagaKelas;
use App\Models\Role;
use Illuminate\Console\Command;

class SyncMonitoringRoles extends Command
{
    protected $signature = 'monitoring:sync-roles {--formal-only : Hanya buat role untuk unit formal (bukan asrama/diniyah)}';

    protected $description = 'Sinkronkan role monitoring per lembaga dari daftar lembaga (v_lembaga_kelas).';

    public function handle(): int
    {
        $lembagaValues = [];

        foreach (LembagaKelas::formal()->distinct()->orderBy('title')->pluck('title') as $title) {
            if ($title) {
                $lembagaValues[] = $title;
            }
        }

        if (!$this->option('formal-only')) {
            foreach (LembagaKelas::asrama()->distinct()->orderBy('idtingkat')->pluck('idtingkat') as $tingkat) {
                if ($tingkat) {
                    $lembagaValues[] = $tingkat;
                }
            }

            foreach (LembagaKelas::madin()->distinct()->orderBy('idtingkat')->pluck('idtingkat') as $tingkat) {
                if ($tingkat) {
                    $lembagaValues[] = $tingkat;
                }
            }
        }

        $lembagaValues = array_values(array_unique($lembagaValues));

        if (empty($lembagaValues)) {
            $this->warn('Tidak ada lembaga yang ditemukan. Periksa view v_lembaga_kelas.');

            return self::SUCCESS;
        }

        $created = 0;
        $existing = 0;

        foreach ($lembagaValues as $lembaga) {
            $name = 'monitoring_' . (str($lembaga)->slug('_')->limit(40, '')->toString() ?: md5($lembaga));

            if (Role::where('name', $name)->exists()) {
                $existing++;

                continue;
            }

            Role::create([
                'name' => $name,
                'guard_name' => 'web',
                'lembaga' => $lembaga,
            ]);
            $created++;
        }

        $this->info("Selesai. Dibuat baru: {$created}, sudah ada: {$existing}, total lembaga: " . count($lembagaValues));

        return self::SUCCESS;
    }
}
