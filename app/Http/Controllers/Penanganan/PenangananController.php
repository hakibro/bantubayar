<?php

namespace App\Http\Controllers\Penanganan;

use App\Http\Controllers\Controller;
use App\Models\Penanganan;
use App\Models\PenangananKesanggupan;
use App\Models\Siswa;
use App\Services\PembayaranService;
use App\Services\SiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;


class PenangananController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->penanganan()->with('siswa');

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
        $siswa = Siswa::findOrFail($id_siswa);
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
            'id_siswa' => 'required|exists:v_siswa,idperson',
            'jenis_penanganan' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $result = \DB::transaction(function () use ($data) {
            $siswa = Siswa::findOrFail($data['id_siswa']);
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
        $siswa = Siswa::findOrFail($penanganan->id_siswa);

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

        $siswa = Siswa::findOrFail($data['id_siswa']);

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
