<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class MonitoringRoleSeeder extends Seeder
{
    /**
     * Buat role monitoring per lembaga berdasarkan daftar lembaga pada v_lembaga_kelas.
     * Logika sebenarnya ada di command `monitoring:sync-roles` agar bisa dipakai ulang.
     */
    public function run(): void
    {
        try {
            Artisan::call('monitoring:sync-roles');

            if ($this->command) {
                $this->command->info(trim(Artisan::output()));
            }
        } catch (\Throwable $e) {
            // Dilewati bila view v_lembaga_kelas belum tersedia (mis. saat migrate:fresh sebelum view dibuat).
            if ($this->command) {
                $this->command->warn('Lewati sinkronisasi role monitoring: ' . $e->getMessage());
            }
        }
    }
}
