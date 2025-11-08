<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Periksa role dan arahkan sesuai
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('pengawas')) {
            return redirect()->route('pengawas.dashboard');
        } else {
            // Default untuk user biasa
            return view('dashboard');
        }
    }
}
