<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengizinkan akses layar monitoring siswa untuk:
 * - admin
 * - monitoring (global, lihat semua)
 * - monitoring per lembaga (isLembagaMonitor)
 */
class AllowMonitoringSiswa
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->hasRole(['admin', 'monitoring']) || $user->isLembagaMonitor())) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
