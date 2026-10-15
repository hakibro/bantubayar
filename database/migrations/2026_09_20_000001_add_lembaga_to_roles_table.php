<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('permission.table_names.roles', 'roles'), function (Blueprint $table) {
            // Nilai lembaga yang diikatkan ke role (mis. nama unit formal, asrama, atau madin).
            // NULL berarti role tidak di-scope ke lembaga tertentu (mis. admin/monitoring).
            $table->string('lembaga')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table(config('permission.table_names.roles', 'roles'), function (Blueprint $table) {
            $table->dropColumn('lembaga');
        });
    }
};
