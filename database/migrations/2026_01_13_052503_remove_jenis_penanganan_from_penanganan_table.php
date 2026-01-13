<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Pastikan kolom ada
        if (Schema::hasColumn('penanganan', 'jenis_penanganan')) {

            // Kosongkan isi kolom dulu (hindari error constraint)
            DB::table('penanganan')->update([
                'jenis_penanganan' => null
            ]);

            Schema::table('penanganan', function (Blueprint $table) {
                $table->dropColumn('jenis_penanganan');
            });
        }
    }

    public function down(): void
    {
        Schema::table('penanganan', function (Blueprint $table) {

            // Kembalikan kolom (sesuaikan tipe aslinya)
            if (!Schema::hasColumn('penanganan', 'jenis_penanganan')) {
                $table->string('jenis_penanganan')->nullable()->after('hasil');
            }
        });
    }
};
