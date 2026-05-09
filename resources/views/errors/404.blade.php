@extends('layouts.dashboard')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full text-center">
            <!-- Illustration -->
            <div class="mb-8">
                <svg class="w-48 h-48 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10a4 4 0 118 0 4 4 0 01-8 0zM21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
                <h1 class="text-6xl md:text-7xl font-black text-slate-900 mb-2">404</h1>
                <p class="text-xl md:text-2xl font-bold text-slate-700 mb-2">Halaman Tidak Ditemukan</p>
                <p class="text-slate-500 mb-8 text-sm md:text-base">
                    Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.
                    Silakan kembali ke halaman utama atau hubungi administrator jika Anda percaya ini adalah kesalahan.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('dashboard') }}"
                        class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition inline-flex items-center justify-center gap-2">
                        <i class="fas fa-home"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>

                    <a href="javascript:history.back()"
                        class="px-6 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition inline-flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 text-slate-500 text-sm">
                <p>Jika masalah terus berlanjut, silakan hubungi tim support.</p>
            </div>
        </div>
    </div>
@endsection
