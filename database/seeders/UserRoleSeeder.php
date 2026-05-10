<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara']);
        Role::firstOrCreate(['name' => 'monitoring']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
            ]
        );

        $petugas = User::firstOrCreate(
            ['email' => 'petugas@example.com'],
            [
                'name' => 'Petugas CS',
                'password' => Hash::make('password'),
            ]
        );

        $bendahara = User::firstOrCreate(
            ['email' => 'bendahara@example.com'],
            [
                'name' => 'Bendahara',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole($adminRole);
        $petugas->assignRole($petugasRole);
        $bendahara->assignRole($bendaharaRole);

        $this->command->info('Role admin, petugas, bendahara, dan monitoring berhasil dibuat.');
    }
}
