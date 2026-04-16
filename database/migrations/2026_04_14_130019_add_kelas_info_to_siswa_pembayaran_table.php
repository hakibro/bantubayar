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
        Schema::table('siswa_pembayaran', function (Blueprint $table) {
            // Menambahkan kolom teks kelas_info setelah kolom periode
            $table->text('kelas_info')->nullable()->after('periode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswa_pembayaran', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn('kelas_info');
        });
    }
};