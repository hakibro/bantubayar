<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\PetugasSiswa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class PetugasController extends Controller
{
    public function index()
    {
        $petugasId = Auth::id();

        // Ambil siswa yang ditangani petugas
        $siswaDitangani = PetugasSiswa::with('siswa')
            ->where('petugas_id', $petugasId)
            ->get();

        return view('petugas.penanganan.index', compact('siswaDitangani'));
    }
}
