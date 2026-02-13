@extends('layouts.dashboard')

@section('title', 'Manage Siswa')

@section('content')
    <div class="max-w-5xl mx-auto p-6">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $siswa->nama }}</h1>
                <p class="text-gray-500">ID Person: {{ $siswa->idperson }}</p>
            </div>


            <a href="{{ route('petugas.siswa') }}"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg shadow hover:bg-gray-300 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        {{-- DATA SISWA --}}
        <div class="bg-white shadow p-5 rounded mb-6">
            <div class="flex justify-between">
                <h2 class="text-lg font-semibold mb-3">Informasi Siswa</h2>

            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <strong>Nama:</strong>
                    <p>{{ $siswa->nama }}</p>
                </div>

                <div>
                    <strong>Lembaga Formal:</strong>
                    <p>{{ $siswa->UnitFormal ?? '-' }} ({{ $siswa->KelasFormal ?? '-' }})</p>
                </div>
                <div>
                    <strong>Asrama:</strong>
                    <p>{{ $siswa->AsramaPondok ?? '-' }} - {{ $siswa->KamarPondok ?? '-' }}</p>
                </div>

                <div>
                    <strong>Terakhir Update Pembayaran:</strong>
                    <p>{{ $siswa->pembayaran->max('updated_at')?->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- PEMBAYARAN --}}
        <div id="pembayaranWrapper">
            @include('petugas.siswa.partials.pembayaran', ['siswa' => $siswa])
        </div>

    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
            <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mr-3"></i>
            <span class="text-lg font-semibold">Sedang memperbarui data pembayaran...</span>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <i class="fas fa-times-circle text-red-600 text-4xl mb-2"></i>
            <h2 class="text-xl font-semibold">Gagal!</h2>
            <p id="errorMessage" class="mt-2"></p>
            <button onclick="closeError()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded">Tutup</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            syncPembayaran({{ $siswa->id }});
        });

        function closeError() {
            document.getElementById("errorModal").classList.add("hidden");
        }
    </script>


@endsection
