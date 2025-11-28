<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role jika belum ada
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara']);

        // Buat user admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        // Buat user petugas
        $petugas = User::firstOrCreate(
            ['email' => 'petugas@example.com'],
            [
                'name' => 'Petugas CS',
                'password' => Hash::make('password'),
            ]
        );
        // Buat user petugas
        $bendahara = User::firstOrCreate(
            ['email' => 'bendahara@example.com'],
            [
                'name' => 'Bendahara',
                'password' => Hash::make('password'),
            ]
        );



        // Assign role
        $admin->assignRole($adminRole);
        $petugas->assignRole($petugasRole);
        $bendahara->assignRole($bendaharaRole);

        $this->command->info('✅ User admin, petugas, dan bendahara berhasil dibuat!');
    }
}
