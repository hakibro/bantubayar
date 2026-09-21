<?php

namespace App\Http\Controllers\Penanganan;

use App\Http\Controllers\Controller;
use App\Models\HomeVisit;
use App\Models\Penanganan;
use App\Models\PenangananKesanggupan;
use App\Services\HomeVisitEligibilityService;
use App\Services\PembayaranService;
use App\Services\SiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;


class PenangananController extends Controller
{
    public function index(Request $request)
    {
        // Hanya tampilkan penanganan yang siswanya masih aktif (ada di v_siswa).
        // Penanganan terhadap alumni ditangani lewat menu Alumni.
        $query = auth()->user()->penanganan()->with('siswa')->whereHas('siswa');

        if ($request->filled('search')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                    ->orWhere('idperson', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', '!=', 'selesai');
        }

        if ($request->filled('waktuDibuat') && is_numeric($request->waktuDibuat)) {
            $hari = (int) $request->waktuDibuat;
            if ($hari > 7) {
                $query->whereDate('created_at', '<=', now()->subDays(8));
            } else {
                $query->whereDate('created_at', now()->subDays($hari));
            }
        }

        if ($request->filled('waktuDiperbarui') && is_numeric($request->waktuDiperbarui)) {
            $hari = (int) $request->waktuDiperbarui;
            if ($hari > 7) {
                $query->whereDate('updated_at', '<=', now()->subDays(8));
            } else {
                $query->whereDate('updated_at', now()->subDays($hari));
            }
        }

        if ($request->filled('terlambat') && is_numeric($request->terlambat)) {
            $hari = (int) $request->terlambat;
            $query->where('status', '!=', 'selesai')
                ->where('updated_at', '<=', now()->subDays($hari)->endOfDay());
        }

        $listPenanganan = $query->orderBy('status', 'asc')->paginate(40);

        if ($request->ajax()) {
            return view('penanganan.partials.list-siswa', compact('listPenanganan'))->render();
        }

        return view('penanganan.index', compact('listPenanganan'));
    }

    public function show(Request $request, PembayaranService $pembayaranService, $id_siswa)
    {
        $siswa = Penanganan::resolveSiswa($id_siswa);

        if (!$siswa) {
            abort(404);
        }

        $pembayaranService->refreshStatusLunasSiswa((string) $siswa->idperson);

        if (auth()->check()) {
            $penanganan = Penanganan::where('id_siswa', $id_siswa)
                ->with('petugas')
                ->orderBy('created_at', 'desc')
                ->get();

            $urlUntukWali = URL::temporarySignedRoute(
                'penanganan.show',
                now()->addDays(30),
                ['id_siswa' => $siswa->idperson]
            );

            $penangananTerakhir = $penanganan->first();
            $riwayatAksi = $penangananTerakhir
                ? $penangananTerakhir->histories()->latest()->get()
                : collect();

            $petugasLogin = Auth::user();
            $summary = $pembayaranService->getSummaryPerPeriode((string) $siswa->idperson);
            $detailPembayaran = $pembayaranService->getDetailPembayaran((string) $siswa->idperson);
            $belumLunas = $pembayaranService->getDetailBelumLunas((string) $siswa->idperson);
            $totalTunggakan = $pembayaranService->getTotalBelumLunas((string) $siswa->idperson);


            return view(
                'penanganan.show',
                compact(
                    'siswa',
                    'penanganan',
                    'riwayatAksi',
                    'penangananTerakhir',
                    'urlUntukWali',
                    'petugasLogin',
                    'summary',
                    'detailPembayaran',
                    'belumLunas',
                    'totalTunggakan'
                )
            );
        }

        if ($request->hasValidSignature()) {
            $belumLunas = $pembayaranService->getDetailBelumLunas((string) $siswa->idperson);
            return view('penanganan.wali_pembayaran', compact('siswa', 'belumLunas'));
        }

        abort(403, 'Akses ditolak. Link tidak valid atau Anda tidak memiliki akses.');
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'id_siswa' => 'required',
            'jenis_penanganan' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $result = \DB::transaction(function () use ($data) {
            $siswa = Penanganan::resolveSiswa($data['id_siswa']);

            if (!$siswa) {
                return [
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan.',
                ];
            }

            $penanganan = Penanganan::getOrCreateForSiswa($siswa);

            if ($penanganan->id_petugas !== Auth::id()) {
                return [
                    'success' => false,
                    'message' => 'Sedang ditangani oleh ' . Auth::user()->name,
                ];
            }

            if (
                $data['jenis_penanganan'] === 'phone' &&
                $penanganan->histories()
                    ->where('jenis_penanganan', 'phone')
                    ->where('created_at', '>=', now()->startOfDay())
                    ->exists()
            ) {
                return [
                    'success' => false,
                    'message' => 'Lakukan telepon ulang di hari berikutnya.',
                ];
            }

            $penanganan->addHistory(
                $data['jenis_penanganan'],
                $data['catatan'] ?? null
            );

            return [
                'success' => true,
                'message' => 'Aksi Penanganan berhasil disimpan',
            ];
        });

        return response()->json($result, $result['success'] ? 200 : 422);
    }


    public function saveHasil(Request $request, PembayaranService $pembayaranService)
    {
        $data = $request->validate([
            'id_penanganan' => 'required|exists:penanganan,id',
            'hasil' => 'required|in:lunas,isi_saldo,cicilan,tidak_ada_respon,hp_tidak_aktif',
            'catatan' => 'nullable|string',
            'rating' => 'nullable|integer|min:0|max:5',
        ]);

        $penanganan = Penanganan::findOrFail($data['id_penanganan']);
        $siswa = Penanganan::resolveSiswa($penanganan->id_siswa);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        $pembayaranService->refreshStatusLunasSiswa((string) $siswa->idperson);
        $siswa->load('statusLunas');

        if ($data['hasil'] === 'lunas') {
            if (!$siswa->is_lunas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status pembayaran siswa belum lunas di sistem keuangan.',
                ], 400);
            }
        }

        if ($data['hasil'] === 'cicilan') {
            $currentTotal = $pembayaranService->getTotalBelumLunas((string) $siswa->idperson);
            $penangananTotal = $penanganan->getTotalTunggakan();

            if ($penangananTotal > 0 && $currentTotal >= $penangananTotal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total kurang bayar saat ini Rp ' . number_format($currentTotal, 0, ',', '.') .
                        ' harus lebih kecil dari saat penanganan dibuat Rp ' .
                        number_format($penangananTotal, 0, ',', '.'),
                ], 400);
            }
        }

        if ($data['hasil'] === 'isi_saldo') {
            if ($siswa->saldo <= $penanganan->saldo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo saat ini Rp ' . number_format($siswa->saldo, 0, ',', '.') .
                        ' harus lebih besar dari saldo saat penanganan dibuat Rp ' .
                        number_format($penanganan->saldo, 0, ',', '.'),
                ], 400);
            }
        }

        if (in_array($data['hasil'], ['tidak_ada_respon', 'hp_tidak_aktif'])) {
            $jumlahChat = $penanganan->histories()->where('jenis_penanganan', 'chat')->count();
            $jumlahTelepon = $penanganan->histories()->where('jenis_penanganan', 'phone')->count();

            if ($jumlahChat < 1 || $jumlahTelepon < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal 1x chat dan 2x telepon sebelum memilih hasil ini.',
                ], 422);
            }

            if ($data['hasil'] === 'hp_tidak_aktif') {
                return response()->json([
                    'success' => false,
                    'action_required' => 'update_nomor_hp',
                    'message' => 'Nomor HP siswa tidak aktif. Silakan perbarui nomor telepon siswa terlebih dahulu.',
                ], 409);
            }
        }

        $penanganan->update([
            'hasil' => $data['hasil'],
            'rating' => $data['rating'],
            'status' => 'selesai',
            'catatan' => $data['catatan'] ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil penanganan berhasil disimpan.',
        ]);
    }

    public function updatePhone(Request $request, SiswaService $siswaService)
    {
        $data = $request->validate([
            'id_siswa' => 'required|exists:v_siswa,idperson',
            'wali' => 'required|string',
            'phone' => 'required|string',
        ]);

        $siswa = Penanganan::resolveSiswa($data['id_siswa']);

        if (!$siswa) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        \DB::transaction(function () use ($data, $siswa) {
            $penanganan = Penanganan::getOrCreateForSiswa($siswa);

            if ($penanganan->id_petugas !== Auth::id()) {
                return;
            }

            $penanganan->addHistory(
                'update phone',
                trim('Update No. HP ke ' . $data['phone'] . ' - ' . ($data['wali'] ?? ''))
            );
        });

        try {
            $siswaService->updateTelepon($siswa->idperson, $data['wali'], $data['phone']);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menyimpan No. HP Wali ' . $siswa->nama . ': ' . $data['phone'] . ' - ' . $data['wali'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Status kelayakan home visit untuk seorang siswa (dipakai UI halaman penanganan).
     */
    public function statusHomeVisit(Request $request, HomeVisitEligibilityService $eligibility, $id_siswa)
    {
        $siswa = Penanganan::resolveSiswa($id_siswa);

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        $hasil = $eligibility->evaluate($siswa);
        $pengajuan = $siswa->pengajuanHomeVisitAktif();

        return response()->json([
            'success' => true,
            'eligible' => $hasil['eligible'],
            'syarat_terpenuhi' => $hasil['syarat_terpenuhi'],
            'syarat_belum' => $hasil['syarat_belum'],
            'boleh_override' => $hasil['boleh_override'],
            'penanganan_id' => $hasil['penanganan_id'],
            'pengajuan' => $pengajuan ? [
                'id' => $pengajuan->id,
                'status' => $pengajuan->status,
                'tanggal' => $pengajuan->created_at?->format('d/m/Y H:i'),
            ] : null,
        ]);
    }

    /**
     * Ajukan home visit untuk siswa yang kategorinya sudah "sulit".
     * Dibuat sebagai pengajuan berstatus pending menunggu persetujuan admin.
     */
    public function ajukanHomeVisit(Request $request, HomeVisitEligibilityService $eligibility)
    {
        $data = $request->validate([
            'id_siswa' => 'required',
            'alasan_override' => 'nullable|string|max:2000',
        ]);

        $siswa = Penanganan::resolveSiswa($data['id_siswa']);

        if (!$siswa) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        // Cegah pengajuan ganda yang masih aktif.
        if ($siswa->pengajuanHomeVisitAktif()) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah ada pengajuan home visit yang aktif untuk siswa ini.',
            ], 422);
        }

        if (!$eligibility->bolehDiajukan($siswa, $data['alasan_override'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa belum memenuhi kategori sulit. Isi alasan untuk override manual.',
            ], 422);
        }

        $penanganan = $siswa->penangananAktif() ?? $siswa->penanganan()->latest()->first();

        $homeVisit = HomeVisit::create([
            'siswa_id' => $siswa->idperson,
            'admin_id' => null,
            'penanganan_id' => $penanganan?->id,
            'diajukan_oleh' => Auth::id(),
            'petugas_nama' => $request->input('petugas_nama'),
            'petugas_hp' => $request->input('petugas_hp'),
            'tanggal_visit' => $request->input('tanggal_visit'),
            'alasan_pengajuan' => $data['alasan_override'] ?? null,
            'token' => \Str::uuid(),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan home visit berhasil dibuat dan menunggu persetujuan admin.',
            'home_visit_id' => $homeVisit->id,
            'status' => $homeVisit->status,
        ]);
    }


    // Kesanggupan
    public function kirimKesanggupan(Request $request)
    {
        try {
            $data = $request->validate([
                'penanganan_id' => 'required|exists:penanganan,id',
                'tanggal_kesanggupan' => 'required|date',
            ]);

            $kesanggupan = PenangananKesanggupan::firstOrCreate(
                [
                    'penanganan_id' => $data['penanganan_id'],
                    'tanggal' => $data['tanggal_kesanggupan'],
                ],
                [
                    'token' => \Str::uuid(),
                ]
            );

            $kesanggupan->penanganan()->update(['status' => 'menunggu_tindak_lanjut']);

            return response()->json([
                'success' => true,
                'is_duplicate' => !$kesanggupan->wasRecentlyCreated,
                'link' => route('wali.kesanggupan.form', $kesanggupan->token)
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }

    public function formKesanggupan($token)
    {
        $kesanggupan = PenangananKesanggupan::where('token', $token)->firstOrFail();
        return view('penanganan.kesanggupan', compact('kesanggupan'));
    }

    public function submitKesanggupan(Request $request, $token)
    {
        $data = $request->validate([
            'nominal' => 'required|numeric|min:1',
        ]);

        $kesanggupan = PenangananKesanggupan::where('token', $token)->firstOrFail();
        $kesanggupan->update(['nominal' => $data['nominal']]);

        return response()->json([
            'success' => true,
            'message' => 'Kesanggupan berhasil dikirim'
        ]);
    }
}
