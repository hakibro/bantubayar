<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $class) {
            // Menambahkan kolom boolean, default false, dan index
            $class->boolean('is_lunas')->default(false)->after('nama')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $class) {
            // Menghapus index dan kolom jika migrasi di-rollback
            $class->dropIndex(['is_lunas']);
            $class->dropColumn('is_lunas');
        });
    }
};