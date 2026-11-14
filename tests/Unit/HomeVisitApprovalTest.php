<?php

use App\Http\Controllers\Admin\HomeVisitController;
use App\Models\HomeVisit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
    DB::purge('sqlite');
    Schema::create('home_visits', function (Blueprint $table) {
        $table->id();
        $table->string('status');
        $table->string('petugas_nama')->nullable();
        $table->string('petugas_hp')->nullable();
        $table->unsignedBigInteger('disetujui_oleh')->nullable();
        $table->timestamp('disetujui_at')->nullable();
        $table->timestamps();
    });
});

it('requires missing officer details before approval', function () {
    $visit = HomeVisit::create(['status' => 'pending', 'petugas_nama' => 'Petugas']);

    try {
        (new HomeVisitController)->approve(Request::create('/', 'POST'), $visit->id);
        $this->fail('Persetujuan tanpa nomor HP harus ditolak.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('petugas_hp');
        expect($visit->fresh()->status)->toBe('pending');
    }
});

it('saves officer details when approving and preserves existing details on quick approval', function () {
    $visit = HomeVisit::create(['status' => 'pending']);
    (new HomeVisitController)->approve(Request::create('/', 'POST', [
        'petugas_nama' => 'Ahmad', 'petugas_hp' => '08123456789',
    ]), $visit->id);
    expect($visit->fresh())->status->toBe('disetujui')->petugas_nama->toBe('Ahmad')->petugas_hp->toBe('08123456789');

    $existing = HomeVisit::create(['status' => 'pending', 'petugas_nama' => 'Ali', 'petugas_hp' => '08123456780']);
    (new HomeVisitController)->approve(Request::create('/', 'POST'), $existing->id);
    expect($existing->fresh())->status->toBe('disetujui')->petugas_nama->toBe('Ali')->petugas_hp->toBe('08123456780');
});
