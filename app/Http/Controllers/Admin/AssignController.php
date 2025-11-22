<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Services\SiswaService;
use Illuminate\Http\Request;

class AssignController extends Controller
{
    function index()
    {

        // Hitung jumlah data siswa
        $count = Siswa::count();

        if ($count === 0) {
            // Tidak ada data siswa → tampilkan tombol sinkronisasi
            return view('admin.assign.get-all-siswa');
        }

        return view('admin.assign.index');
    }

}
