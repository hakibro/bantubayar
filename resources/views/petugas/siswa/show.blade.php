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
            <h2 class="text-lg font-semibold mb-3">Informasi Siswa</h2>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <strong>Nama:</strong>
                    <p>{{ $siswa->nama }}</p>
                </div>
                <div>
                    <strong>Lembaga Formal:</strong>
                    <p>{{ $siswa->unit_formal ?? '-' }} ({{ $siswa->kelas_formal ?? '-' }})</p>
                </div>
                <div>
                    <strong>Asrama:</strong>
                    <p>{{ $siswa->AsramaPondok ?? '-' }} - {{ $siswa->KamarPondok ?? '-' }}</p>
                </div>
                <div>
                    <strong>Status Pembayaran:</strong>
                    <p class="{{ $siswa->is_lunas ? 'text-green-600' : 'text-red-600' }} font-semibold">
                        {{ $siswa->is_lunas ? 'Lunas' : 'Belum Lunas' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- PEMBAYARAN --}}
        <div id="pembayaranWrapper">
            @include('petugas.siswa.partials.pembayaran', ['siswa' => $siswa, 'summary' => $summary, 'belumLunas' => $belumLunas])
        </div>

    </div>
@endsection
