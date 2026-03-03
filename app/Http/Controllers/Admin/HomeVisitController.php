<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeVisit;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HomeVisitController extends Controller
{
    /**
     * Halaman pilih siswa untuk home visit
     */
    public function select(Request $request)
    {
        $query = Siswa::query();

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('idperson', 'like', "%{$search}%");
            });
        }

        // Filter lembaga
        if ($request->filled('lembaga')) {
            if ($request->lembaga === '__NULL__') {
                $query->whereNull('UnitFormal');
            } else {
                $query->where('UnitFormal', $request->lembaga);
            }
        }

        // Filter kelas
        if ($request->filled('kelas')) {
            $query->where('KelasFormal', $request->kelas);
        }

        // Filter asrama
        if ($request->filled('asrama')) {
            if ($request->asrama === '__NULL__') {
                $query->whereNull('AsramaPondok');
            } else {
                $query->where('AsramaPondok', $request->asrama);
            }
        }

        // Filter kamar
        if ($request->filled('kamar')) {
            $query->where('KamarPondok', $request->kamar);
        }

        $siswa = $query->orderBy('nama')->paginate(40)->withQueryString();

        // Data untuk dropdown filter
        $daftarLembaga = Siswa::select('UnitFormal')->distinct()->pluck('UnitFormal')->filter()->sort()->values();
        $daftarAsrama = Siswa::select('AsramaPondok')->distinct()->pluck('AsramaPondok')->filter()->sort()->values();

        // Jika request AJAX, kembalikan partial table
        if ($request->ajax()) {
            return view('admin.home-visit.partials.table', compact('siswa'))->render();
        }

        return view('admin.home-visit.select', compact('siswa', 'daftarLembaga', 'daftarAsrama'));
    }

    /**
     * Form tambah home visit untuk siswa tertentu
     */
    public function create(Request $request)
    {
        $request->validate(['siswa_id' => 'required|exists:siswa,id']);
        $siswa = Siswa::findOrFail($request->siswa_id);

        return view('admin.home-visit.create', compact('siswa'));
    }

    /**
     * Simpan home visit baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'petugas_nama' => 'required|string|max:255',
            'petugas_hp' => 'required|string|max:20',
            'tanggal_visit' => 'nullable|date',
        ]);

        $homeVisit = HomeVisit::create([
            'siswa_id' => $request->siswa_id,
            'admin_id' => auth()->id(),
            'petugas_nama' => $request->petugas_nama,
            'petugas_hp' => $request->petugas_hp,
            'tanggal_visit' => $request->tanggal_visit,
            'token' => Str::uuid(),
            'status' => 'dijadwalkan',
        ]);

        return redirect()->route('admin.home-visit.show', $homeVisit->id)
            ->with('success', 'Home visit berhasil dijadwalkan.');
    }

    /**
     * Detail home visit
     */
    public function show($id)
    {
        $homeVisit = HomeVisit::with('siswa')->findOrFail($id);
        return view('admin.home-visit.show', compact('homeVisit'));
    }

    /**
     * Cetak surat tugas PDF
     */
    public function cetak($id)
    {
        $homeVisit = HomeVisit::with('siswa')->findOrFail($id);
        $pdf = Pdf::loadView('admin.home-visit.cetak', compact('homeVisit'));
        return $pdf->download('surat-tugas-' . $homeVisit->siswa->nama . '.pdf');
    }

    /**
     * Ambil data kelas untuk filter (AJAX)
     */
    public function kelas(Request $request)
    {
        $lembaga = $request->lembaga;
        $kelas = Siswa::when($lembaga, fn($q) => $q->where('UnitFormal', $lembaga))
            ->select('KelasFormal')
            ->distinct()
            ->whereNotNull('KelasFormal')
            ->orderBy('KelasFormal')
            ->pluck('KelasFormal');

        return response()->json($kelas);
    }

    /**
     * Ambil data kamar untuk filter (AJAX)
     */
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