@extends('layouts.dashboard')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center px-4 py-12">
        <div class="max-w-lg w-full text-center">
            <!-- Illustration -->
            <div class="mb-8">
                <svg class="w-48 h-48 mx-auto text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <!-- Content -->
            <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12">
                <h1 class="text-6xl md:text-7xl font-black text-slate-900 mb-2">419</h1>
                <p class="text-xl md:text-2xl font-bold text-slate-700 mb-2">Token Sesi Expired</p>
                <p class="text-slate-500 mb-8 text-sm md:text-base">
                    Sesi Anda telah kadaluarsa. Silakan muat ulang halaman dan coba lagi.
                    Jika masalah terus berlanjut, silakan refresh browser atau login kembali.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="location.reload()"
                        class="px-6 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition inline-flex items-center justify-center gap-2">
                        <i class="fas fa-redo"></i>
                        <span>Muat Ulang Halaman</span>
                    </button>

                    <a href="{{ route('login') }}"
                        class="px-6 py-3 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition inline-flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Login Kembali</span>
                    </a>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 text-slate-500 text-sm">
                <p>Hubungi tim support jika masalah berlanjut.</p>
            </div>
        </div>
    </div>
@endsection
