<?php

use App\Models\Penanganan;
use App\Models\PenangananHistory;
use App\Models\Siswa;
use App\Services\HomeVisitEligibilityService;

/*
 * Unit test untuk HomeVisitEligibilityService.
 * Menggunakan Mockery untuk men-simulasikan relasi tanpa perlu database.
 * Service diakses lewat properti relasi (histories, kesanggupanTerakhir, phone),
 * sehingga cukup men-stub properti tersebut.
 */

function makePhoneModel(?string $phone): object
{
    return new class($phone) {
        public function __construct(public ?string $phone)
        {
        }
    };
}

function makeSiswa(array $opts = []): Siswa
{
    $siswa = Mockery::mock(Siswa::class)->makePartial();
    $siswa->shouldReceive('penangananAktif')->andReturn($opts['penangananAktif'] ?? null);

    // penanganan() mengembalikan builder yang bisa di-chain (latest()->first()).
    $penangananFallback = $opts['penangananFallback'] ?? null;
    $builder = Mockery::mock();
    $builder->shouldReceive('latest')->andReturnSelf();
    $builder->shouldReceive('first')->andReturn($penangananFallback);
    $siswa->shouldReceive('penanganan')->andReturn($builder);

    // Atribut dasar.
    $siswa->idperson = 'P001';
    $siswa->is_lunas = $opts['is_lunas'] ?? false;
    $siswa->phone = array_key_exists('phone', $opts) && $opts['phone'] === null
        ? null
        : makePhoneModel($opts['phone'] ?? '08123456789');

    return $siswa;
}

function makePenanganan(array $opts = []): Penanganan
{
    $p = Mockery::mock(Penanganan::class)->makePartial();
    $p->id = 1;
    $p->status = $opts['status'] ?? 'menunggu_respon';
    $p->hasil = $opts['hasil'] ?? null;
    $p->updated_at = $opts['updated_at'] ?? now();
    $p->histories = $opts['histories'] ?? collect();
    $p->kesanggupanTerakhir = $opts['kesanggupanTerakhir'] ?? null;

    return $p;
}

function makeHistory(string $jenis): PenangananHistory
{
    $h = new PenangananHistory();
    $h->jenis_penanganan = $jenis;
    return $h;
}

function makeKesanggupan(string $tanggal): object
{
    return new class($tanggal) {
        public function __construct(public string $tanggal)
        {
        }
    };
}

it('memenuhi kategori hp kosong ketika siswa tidak punya nomor hp', function () {
    $siswa = makeSiswa(['phone' => null]);

    $hasil = (new HomeVisitEligibilityService())->evaluate($siswa);

    expect($hasil['eligible'])->toBeTrue();
    expect(collect($hasil['syarat_terpenuhi'])->pluck('key')->all())->toContain('hp_kosong');
});

it('memenuhi kategori telepon tidak tersambung setelah 2x telepon', function () {
    $penanganan = makePenanganan([
        'histories' => collect([makeHistory('phone'), makeHistory('phone')]),
        'hasil' => 'tidak_ada_respon',
    ]);
    $siswa = makeSiswa(['penangananAktif' => $penanganan]);

    $hasil = (new HomeVisitEligibilityService())->evaluate($siswa);

    expect($hasil['eligible'])->toBeTrue();
    expect(collect($hasil['syarat_terpenuhi'])->pluck('key')->all())->toContain('telepon_tidak_tersambung');
});

it('memenuhi kategori hp tidak aktif', function () {
    $penanganan = makePenanganan(['hasil' => 'hp_tidak_aktif']);
    $siswa = makeSiswa(['penangananAktif' => $penanganan]);

    $hasil = (new HomeVisitEligibilityService())->evaluate($siswa);

    expect(collect($hasil['syarat_terpenuhi'])->pluck('key')->all())->toContain('hp_tidak_aktif');
});

it('memenuhi kategori mandeg setelah 7 hari tanpa pergerakan', function () {
    $penanganan = makePenanganan([
        'status' => 'menunggu_respon',
        'updated_at' => now()->subDays(10),
    ]);
    $siswa = makeSiswa(['penangananAktif' => $penanganan]);

    $hasil = (new HomeVisitEligibilityService())->evaluate($siswa);

    expect(collect($hasil['syarat_terpenuhi'])->pluck('key')->all())->toContain('mandeg');
});

it('memenuhi kategori kesanggupan terlewat', function () {
    $penanganan = makePenanganan([
        'status' => 'menunggu_tindak_lanjut',
        'kesanggupanTerakhir' => makeKesanggupan(now()->subDays(3)->toDateString()),
    ]);
    $siswa = makeSiswa(['penangananAktif' => $penanganan, 'is_lunas' => false]);

    $hasil = (new HomeVisitEligibilityService())->evaluate($siswa);

    expect(collect($hasil['syarat_terpenuhi'])->pluck('key')->all())->toContain('kesanggupan_lewat');
});

it('tidak eligible ketika penanganan masih baru dan punya hp', function () {
    $penanganan = makePenanganan([
        'status' => 'menunggu_respon',
        'updated_at' => now(),
    ]);
    $siswa = makeSiswa(['penangananAktif' => $penanganan, 'phone' => '08123456789']);

    $hasil = (new HomeVisitEligibilityService())->evaluate($siswa);

    expect($hasil['eligible'])->toBeFalse();
});

it('mengizinkan override manual saat tidak eligible bila ada alasan', function () {
    $penanganan = makePenanganan(['status' => 'menunggu_respon', 'updated_at' => now()]);
    $siswa = makeSiswa(['penangananAktif' => $penanganan, 'phone' => '08123456789']);

    $service = new HomeVisitEligibilityService();

    expect($service->bolehDiajukan($siswa, ''))->toBeFalse();
    expect($service->bolehDiajukan($siswa, 'Wali sulit dihubungi'))->toBeTrue();
});
