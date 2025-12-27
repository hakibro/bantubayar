<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penanganan', function (Blueprint $table) {

            // jenis_penanganan
            DB::statement("
                ALTER TABLE penanganan 
                MODIFY jenis_penanganan ENUM(
                    'chat',
                    'telepon',
                    'telepon_ulang',
                    'visit'
                ) 
                CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci
            ");

            // hasil
            DB::statement("
                ALTER TABLE penanganan 
                MODIFY hasil ENUM(
                    'lunas',
                    'rekom_isi_saldo',
                    'rekom_tidak_isi_saldo',
                    'tidak_ada_respon'
                ) 
                CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci
                NULL
            ");

            // status
            DB::statement("
                ALTER TABLE penanganan 
                MODIFY status ENUM(
                    'menunggu_respon',
                    'menunggu_tindak_lanjut',
                    'aktif',
                    'selesai'
                ) 
                CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci
            ");

            // rating → tinyint
            $table->tinyInteger('rating')
                ->nullable()
                ->comment('1=sangat tidak kooperatif, 5=sangat kooperatif')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('penanganan', function (Blueprint $table) {

            DB::statement("
                ALTER TABLE penanganan 
                MODIFY jenis_penanganan ENUM('chat','telepon','visit')
            ");

            DB::statement("
                ALTER TABLE penanganan 
                MODIFY hasil ENUM('lunas','rekom') NULL
            ");

            DB::statement("
                ALTER TABLE penanganan 
                MODIFY status ENUM('belum','proses','selesai')
            ");

            $table->json('rating')->nullable()->change();
        });
    }
};

