<?php

namespace App\Services;

use App\Models\Penanganan;
use App\Models\Siswa;

/**
 * Menentukan apakah seorang siswa sudah masuk kategori "sulit" sehingga
 * layak diajukan home visit. Kategori dihitung otomatis dari data penanganan
 * (histories, hasil, umur penanganan) dan data siswa (no. HP, kesanggupan).
 *
 * Hasil dipakai di dua tempat:
 *  - UI halaman penanganan (mengaktifkan/menonaktifkan tombol Ajukan Home Visit)
 *  - Validasi server saat menyimpan pengajuan (tidak bisa di-bypass dari klien)
 *
 * Ada juga jalur override manual: bila petugas merasa situasinya sulit walau
 * tidak ada satu pun kategori otomatis yang terpenuhi.
 */
class HomeVisitEligibilityService
{
    /** Minimal jumlah percobaan telepon untuk kategori tidak tersambung. */
    public const MIN_TELEPON = 2;

    /** Minimal jumlah chat untuk kategori tidak ada balasan. */
    public const MIN_CHAT = 1;

    /** Ambang hari tanpa pergerakan penanganan. */
    public const HARI_MANDEG = 7;

    /**
     * Evaluasi kelayakan home visit untuk seorang siswa.
     *
     * @return array{
     *     eligible: bool,
     *     syarat_terpenuhi: array<int, array{key:string, label:string, keterangan:string}>,
     *     syarat_belum: array<int, array{key:string, label:string, keterangan:string}>,
     *     boleh_override: bool,
     *     penanganan_id: int|null
     * }
     */
    public function evaluate(Siswa $siswa): array
    {
        $penanganan = $siswa->penangananAktif() ?? $siswa->penanganan()->latest()->first();

        $penangananId = $penanganan?->id;
        $histories = $penanganan?->histories ?? collect();

        $jumlahChat = $histories->where('jenis_penanganan', 'chat')->count();
        $jumlahTelepon = $histories->where('jenis_penanganan', 'phone')->count();
        $jumlahHomeVisit = $histories->where('jenis_penanganan', 'home_visit')->count();

        $hasilTerakhir = $penanganan?->hasil;
        $punyaHp = $siswa->phone?->phone !== null && trim((string) $siswa->phone?->phone) !== '';

        $terpenuhi = [];
        $belum = [];

        // 1. No. HP wali kosong di data.
        if (!$punyaHp) {
            $terpenuhi[] = [
                'key' => 'hp_kosong',
                'label' => 'Nomor HP wali tidak ada',
                'keterangan' => 'Data siswa belum memiliki nomor HP wali.',
            ];
        } else {
            $belum[] = [
                'key' => 'hp_kosong',
                'label' => 'Nomor HP wali tidak ada',
                'keterangan' => 'Nomor HP wali tersedia, coba hubungi terlebih dahulu.',
            ];
        }

        // 2. Telepon tidak tersambung / tidak dijawab.
        $hasilTanpaRespon = in_array($hasilTerakhir, ['tidak_ada_respon', 'hp_tidak_aktif'], true);
        if ($jumlahTelepon >= self::MIN_TELEPON && ($hasilTanpaRespon || $jumlahTelepon >= self::MIN_TELEPON)) {
            if ($jumlahTelepon >= self::MIN_TELEPON) {
                $terpenuhi[] = [
                    'key' => 'telepon_tidak_tersambung',
                    'label' => 'Telepon tidak tersambung/tidak dijawab',
                    'keterangan' => "Sudah {$jumlahTelepon}x percobaan telepon tanpa hasil.",
                ];
            }
        }
        if (!$this->sudahTerpenuhi($terpenuhi, 'telepon_tidak_tersambung')) {
            $belum[] = [
                'key' => 'telepon_tidak_tersambung',
                'label' => 'Telepon tidak tersambung/tidak dijawab',
                'keterangan' => 'Minimal ' . self::MIN_TELEPON . 'x telepon. Saat ini: ' . $jumlahTelepon . 'x.',
            ];
        }

        // 3. HP tidak aktif.
        if ($hasilTerakhir === 'hp_tidak_aktif') {
            $terpenuhi[] = [
                'key' => 'hp_tidak_aktif',
                'label' => 'Nomor HP tidak aktif',
                'keterangan' => 'Hasil penanganan terakhir menyatakan nomor HP tidak aktif.',
            ];
        } else {
            $belum[] = [
                'key' => 'hp_tidak_aktif',
                'label' => 'Nomor HP tidak aktif',
                'keterangan' => 'Belum ada hasil penanganan "HP tidak aktif".',
            ];
        }

        // 4. Sudah ditangani tetapi mandeg (tidak ada pembayaran/kabar).
        $mandeg = $penanganan
            && $penanganan->status !== 'selesai'
            && $penanganan->updated_at
            && $penanganan->updated_at->lte(now()->subDays(self::HARI_MANDEG));
        if ($mandeg) {
            $terpenuhi[] = [
                'key' => 'mandeg',
                'label' => 'Sudah ditangani tetapi tidak ada kabar',
                'keterangan' => 'Tidak ada pergerakan penanganan selama ' . self::HARI_MANDEG . ' hari terakhir.',
            ];
        } else {
            $belum[] = [
                'key' => 'mandeg',
                'label' => 'Sudah ditangani tetapi tidak ada kabar',
                'keterangan' => 'Penanganan masih baru (belum ' . self::HARI_MANDEG . ' hari).',
            ];
        }

        // 5. Chat tersampaikan tanpa balasan.
        if ($jumlahChat >= self::MIN_CHAT && $hasilTanpaRespon) {
            $terpenuhi[] = [
                'key' => 'chat_tanpa_balasan',
                'label' => 'Chat tanpa balasan',
                'keterangan' => 'Sudah ' . $jumlahChat . 'x chat tanpa respon wali.',
            ];
        } else {
            $belum[] = [
                'key' => 'chat_tanpa_balasan',
                'label' => 'Chat tanpa balasan',
                'keterangan' => 'Perlu minimal ' . self::MIN_CHAT . 'x chat dengan hasil tanpa respon.',
            ];
        }

        // 6. Kesanggupan terlewat & belum ada pembayaran.
        $kesanggupan = $penanganan?->kesanggupanTerakhir;
        $kesanggupanLewat = $kesanggupan
            && $kesanggupan->tanggal
            && \Carbon\Carbon::parse($kesanggupan->tanggal)->lt(now()->startOfDay())
            && !$siswa->is_lunas;
        if ($kesanggupanLewat) {
            $terpenuhi[] = [
                'key' => 'kesanggupan_lewat',
                'label' => 'Kesanggupan terlewat',
                'keterangan' => 'Wali berjanji membayar pada ' . $kesanggupan->tanggal . ' tetapi belum terealisasi.',
            ];
        } else {
            $belum[] = [
                'key' => 'kesanggupan_lewat',
                'label' => 'Kesanggupan terlewat',
                'keterangan' => 'Belum ada kesanggupan yang terlewat.',
            ];
        }

        $eligible = count($terpenuhi) > 0;

        return [
            'eligible' => $eligible,
            'syarat_terpenuhi' => $terpenuhi,
            'syarat_belum' => $belum,
            'boleh_override' => !$eligible,
            'penanganan_id' => $penangananId,
        ];
    }

    /**
     * Validasi pengajuan. Mengembalikan true bila boleh diajukan.
     * Override manual (ada alasan) selalu diizinkan selama belum pernah di-home-visit.
     */
    public function bolehDiajukan(Siswa $siswa, ?string $alasanOverride = null): bool
    {
        $hasil = $this->evaluate($siswa);

        if ($hasil['eligible']) {
            return true;
        }

        return trim((string) $alasanOverride) !== '';
    }

    private function sudahTerpenuhi(array $items, string $key): bool
    {
        foreach ($items as $item) {
            if ($item['key'] === $key) {
                return true;
            }
        }

        return false;
    }
}
