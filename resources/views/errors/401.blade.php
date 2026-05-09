@extends('layouts.dashboard')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full text-center">
            <!-- Illustration -->
            <div class="mb-8">
                <svg class="w-48 h-48 mx-auto text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
                <h1 class="text-6xl md:text-7xl font-black text-slate-900 mb-2">401</h1>
                <p class="text-xl md:text-2xl font-bold text-slate-700 mb-2">Autentikasi Diperlukan</p>
                <p class="text-slate-500 mb-8 text-sm md:text-base">
                    Sesi Anda telah berakhir atau Anda belum login.
                    Silakan login terlebih dahulu untuk mengakses halaman ini.
                </p>

                <!-- Action Button -->
                <div class="flex justify-center">
                    <a href="{{ route('login') }}"
                        class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition inline-flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login Sekarang</span>
                    </a>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 text-slate-500 text-sm">
                <p>Hubungi administrator jika Anda memerlukan bantuan.</p>
            </div>
        </div>
    </div>
@endsection
