@extends('layouts.dashboard')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-6">Dashboard Admin</h1>

        <!-- Statistik Utama -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Siswa -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Total Siswa</p>
                        <p class="text-3xl font-bold">{{ number_format($totalSiswa) }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-users text-blue-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Siswa Lunas -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Siswa Lunas</p>
                        <p class="text-3xl font-bold">{{ number_format($siswaLunas) }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">{{ number_format(($siswaLunas / max($totalSiswa, 1)) * 100, 1) }}% dari
                    total</p>
            </div>

            <!-- Siswa Belum Lunas -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-red-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Belum Lunas</p>
                        <p class="text-3xl font-bold">{{ number_format($siswaBelumLunas) }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Siswa Belum Sync -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-yellow-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Belum Sync</p>
                        <p class="text-3xl font-bold">{{ number_format($siswaBelumSync) }}</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <i class="fas fa-sync-alt text-yellow-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Baris Kedua -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Petugas -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-purple-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Petugas</p>
                        <p class="text-3xl font-bold">{{ number_format($totalPetugas) }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <i class="fas fa-user-tie text-purple-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Bendahara -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-indigo-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Bendahara</p>
                        <p class="text-3xl font-bold">{{ number_format($totalBendahara) }}</p>
                    </div>
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-calculator text-indigo-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Penanganan Aktif -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-orange-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Penanganan Aktif</p>
                        <p class="text-3xl font-bold">{{ number_format($penangananAktif) }}</p>
                    </div>
                    <div class="bg-orange-100 p-3 rounded-full">
                        <i class="fas fa-phone-alt text-orange-500 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Home Visit Aktif -->
            <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-teal-500">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Home Visit Aktif</p>
                        <p class="text-3xl font-bold">{{ number_format($homeVisitAktif) }}</p>
                    </div>
                    <div class="bg-teal-100 p-3 rounded-full">
                        <i class="fas fa-home text-teal-500 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik / Informasi Tambahan (Opsional) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Progress Sinkronisasi Pembayaran -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-lg mb-4">Progress Sinkronisasi Pembayaran</h3>
                @php
                    $totalSync = \App\Models\Siswa::has('pembayaran')->count();
                    $percentSync = $totalSiswa > 0 ? round(($totalSync / $totalSiswa) * 100, 1) : 0;
                @endphp
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-primary h-4 rounded-full" style="width: {{ $percentSync }}%"></div>
                    </div>
                    <span class="ml-3 font-bold">{{ $percentSync }}%</span>
                </div>
                <p class="text-sm text-gray-500 mt-2">{{ number_format($totalSync) }} dari
                    {{ number_format($totalSiswa) }} siswa sudah tersinkronisasi</p>
                <a href="{{ route('admin.sync-pembayaran.index') }}"
                    class="inline-block mt-4 text-primary hover:underline">Kelola Sinkronisasi →</a>
            </div>

            <!-- Tautan Cepat -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-lg mb-4">Tautan Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.siswa.index') }}"
                        class="bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-user-graduate text-blue-500 mr-2"></i> Data Siswa
                    </a>
                    <a href="{{ route('admin.petugas.index') }}"
                        class="bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-user-tie text-purple-500 mr-2"></i> Kelola Petugas
                    </a>
                    <a href="{{ route('admin.assign.index') }}"
                        class="bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-link text-green-500 mr-2"></i> Assign Petugas
                    </a>
                    <a href="{{ route('admin.home-visit.select') }}"
                        class="bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-home text-teal-500 mr-2"></i> Home Visit
                    </a>
                    <a href="{{ route('admin.sync-pembayaran.index') }}"
                        class="bg-gray-50 p-3 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-sync text-yellow-500 mr-2"></i> Sinkronisasi
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
