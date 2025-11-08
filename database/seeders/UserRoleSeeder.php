<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role jika belum ada
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $petugasRole = Role::firstOrCreate(['name' => 'petugas']);

        // (Opsional) Buat permission dasar
        $permissions = [
            'manage users',
            'view dashboard',
            'manage reports'
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Beri semua izin ke admin
        $adminRole->givePermissionTo(Permission::all());

        // Beri izin tertentu ke petugas
        $petugasRole->givePermissionTo(['view dashboard', 'manage reports']);

        // Buat user admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'), // ubah jika perlu
            ]
        );

        // Buat user petugas
        $petugas = User::firstOrCreate(
            ['email' => 'petugas@example.com'],
            [
                'name' => 'Petugas CS',
                'password' => Hash::make('password'), // ubah jika perlu
            ]
        );

        // Assign role ke masing-masing user
        $admin->assignRole($adminRole);
        $petugas->assignRole($petugasRole);

        $this->command->info('✅ User admin & petugas berhasil dibuat!');
    }
}
