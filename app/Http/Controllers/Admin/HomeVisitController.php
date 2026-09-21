<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeVisit;
use App\Models\Siswa;
use App\Services\PembayaranService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeVisitController extends Controller
{
    public function index(Request $request)
    {
        $query = HomeVisit::with(['siswa', 'pengaju']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idperson', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $homeVisits = $query->orderBy('created_at', 'desc')->paginate(40)->withQueryString();

        if ($request->ajax()) {
            return view('admin.home-visit.partials.list', compact('homeVisits'))->render();
        }

        return view('admin.home-visit.index', compact('homeVisits'));
    }

    public function approve($id)
    {
        $homeVisit = HomeVisit::findOrFail($id);

        if ($homeVisit->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $homeVisit->update([
            'status' => 'disetujui',
            'disetujui_oleh' => auth()->id(),
            'disetujui_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan home visit disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['alasan' => 'nullable|string|max:2000']);
        $homeVisit = HomeVisit::findOrFail($id);

        if ($homeVisit->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $homeVisit->update([
            'status' => 'ditolak',
            'disetujui_oleh' => auth()->id(),
            'disetujui_at' => now(),
        ]);

        return back()->with('success', 'Pengajuan home visit ditolak.');
    }

    public function batal($id)
    {
        $homeVisit = HomeVisit::findOrFail($id);

        if (in_array($homeVisit->status, ['selesai', 'ditolak', 'batal'], true)) {
            return back()->with('error', 'Home visit tidak dapat dibatalkan.');
        }

        $homeVisit->update(['status' => 'batal']);

        return back()->with('success', 'Home visit dibatalkan.');
    }

    public function select(Request $request)
    {
        $query = Siswa::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idperson', 'like', "%{$search}%");
            });
        }

        if ($request->filled('lembaga')) {
            if ($request->lembaga === '__NULL__') {
                $query->whereNull('unit_formal');
            } else {
                $query->where('unit_formal', $request->lembaga);
            }
        }

        if ($request->filled('kelas')) {
            $query->where('kelas_formal', $request->kelas);
        }

        if ($request->filled('asrama')) {
            if ($request->asrama === '__NULL__') {
                $query->whereNull('AsramaPondok');
            } else {
                $query->where('AsramaPondok', $request->asrama);
            }
        }

        if ($request->filled('kamar')) {
            $query->where('KamarPondok', $request->kamar);
        }

        $siswa = $query->orderBy('nama')->paginate(40)->withQueryString();

        $daftarLembaga = Siswa::select('unit_formal')->distinct()->pluck('unit_formal')->filter()->sort()->values();
        $daftarAsrama  = Siswa::select('AsramaPondok')->distinct()->pluck('AsramaPondok')->filter()->sort()->values();

        if ($request->ajax()) {
            return view('admin.home-visit.partials.table', compact('siswa'))->render();
        }

        return view('admin.home-visit.select', compact('siswa', 'daftarLembaga', 'daftarAsrama'));
    }

    public function create(Request $request, PembayaranService $pembayaranService)
    {
        $request->validate(['siswa_id' => 'required|exists:v_siswa,idperson']);
        $siswa = Siswa::findOrFail($request->siswa_id);
        $totalTunggakan = $pembayaranService->getTotalBelumLunas((string) $siswa->idperson);

        return view('admin.home-visit.create', compact('siswa', 'totalTunggakan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siswa_id'     => 'required|exists:v_siswa,idperson',
            'petugas_nama' => 'required|string|max:255',
            'petugas_hp'   => 'required|string|max:20',
            'tanggal_visit' => 'nullable|date',
        ]);

        $homeVisit = HomeVisit::create([
            'siswa_id'     => $request->siswa_id,
            'admin_id'     => auth()->id(),
            'petugas_nama' => $request->petugas_nama,
            'petugas_hp'   => $request->petugas_hp,
            'tanggal_visit' => $request->tanggal_visit,
            'token'        => Str::uuid(),
            'status'       => 'dijadwalkan',
        ]);

        return redirect()->route('admin.home-visit.show', $homeVisit->id)
            ->with('success', 'Home visit berhasil dijadwalkan.');
    }

    public function show($id)
    {
        $homeVisit = HomeVisit::with('siswa')->findOrFail($id);
        return view('admin.home-visit.show', compact('homeVisit'));
    }

    public function cetak($id, PembayaranService $pembayaranService)
    {
        $homeVisit = HomeVisit::with('siswa')->findOrFail($id);
        $totalTunggakan = $pembayaranService->getTotalBelumLunas((string) $homeVisit->siswa->idperson);
        $pdf = Pdf::loadView('admin.home-visit.cetak', compact('homeVisit', 'totalTunggakan'));
        return $pdf->download('surat-tugas-' . $homeVisit->siswa->nama . '.pdf');
    }

    public function kelas(Request $request)
    {
        $lembaga = $request->lembaga;
        $kelas = Siswa::when($lembaga, fn($q) => $q->where('unit_formal', $lembaga))
            ->select('kelas_formal')
            ->distinct()
            ->whereNotNull('kelas_formal')
            ->orderBy('kelas_formal')
            ->pluck('kelas_formal');

        return response()->json($kelas);
    }

    public function kamar(Request $request)
    {
        $asrama = $request->asrama;
        $kamar = Siswa::when($asrama, fn($q) => $q->where('AsramaPondok', $asrama))
            ->select('KamarPondok')
            ->distinct()
            ->whereNotNull('KamarPondok')
            ->orderBy('KamarPondok')
            ->pluck('KamarPondok');

        return response()->json($kamar);
    }
}
