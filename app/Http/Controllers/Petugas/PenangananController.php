<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PenangananController extends Controller
{
    public function index()
    {
        return view('petugas.penanganan.index');
    }
}
