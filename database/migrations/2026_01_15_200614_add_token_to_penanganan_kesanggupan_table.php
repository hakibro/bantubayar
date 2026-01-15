<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penanganan_kesanggupan', function (Blueprint $table) {
            $table->uuid('token')
                ->unique()
                ->after('penanganan_id');
        });
    }

    public function down(): void
    {
        Schema::table('penanganan_kesanggupan', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn('token');
        });
    }
};
