<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            ALTER TABLE penanganan 
            MODIFY hasil ENUM(
                'lunas',
                'isi_saldo',
                'rekomendasi',
                'tidak_ada_respon',
                'cicilan',
                'hp_tidak_aktif'
            ) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE penanganan 
            MODIFY hasil ENUM(
                'lunas',
                'isi_saldo',
                'rekomendasi',
                'tidak_ada_respon'
            ) NULL
        ");
    }
};
