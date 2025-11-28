<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Http\Request;

class BendaharaController extends Controller
{
    public function index(Request $request)
    {
        // Ambil lembaga milik user yang sedang login
        $lembaga = auth()->user()->lembaga;

        $query = Siswa::query();

        // Filter berdasarkan lembaga user
        $query->where('UnitFormal', $lembaga)
            ->orWhere('AsramaPondok', $lembaga)
            ->orWhere('TingkatDiniyah', $lembaga);

        // Jika ada pencarian
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('idperson', 'like', "%{$request->search}%");
            });
        }

        $siswa = $query->paginate(40);

        return view('bendahara.penanganan.index', compact('siswa', 'lembaga'));
    }

    public function show($id)
    {
        $siswa = Siswa::with([
            'pembayaran' => function ($q) {
                $q->orderBy('periode', 'desc');
            }
        ])->findOrFail($id);

        return view('bendahara.penanganan.show', compact('siswa'));
    }
}
