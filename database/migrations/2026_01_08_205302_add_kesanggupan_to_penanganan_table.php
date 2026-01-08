<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penanganan', function (Blueprint $table) {
            $table->decimal('kesanggupan', 15, 2)
                ->nullable()
                ->after('tanggal_rekom');
        });
    }

    public function down(): void
    {
        Schema::table('penanganan', function (Blueprint $table) {
            $table->dropColumn('kesanggupan');
        });
    }
};

