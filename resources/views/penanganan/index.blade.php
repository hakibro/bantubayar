@extends('layouts.dashboard')

@section('title', 'Daftar Penanganan')

@section('content')
    <div class="p-6 w-full bg-gray-100">

        {{-- ===== TABLE (Desktop) ===== --}}
        <div class="hidden md:block">
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border">Siswa</th>
                        <th class="p-2 border">Petugas</th>
                        <th class="p-2 border">Jenis</th>
                        <th class="p-2 border">Status</th>
                        <th class="p-2 border">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $row)
                        <tr>
                            <td class="border p-2">{{ $row->siswa->nama }}</td>
                            <td class="border p-2">{{ $row->petugas->name }}</td>
                            <td class="border p-2">{{ ucfirst($row->jenis_penanganan) }}</td>
                            <td class="border p-2">{{ ucfirst($row->status) }}</td>
                            <td class="border p-2">
                                <a href="{{ route('penanganan.show', $row->siswa->id) }}"
                                    class="text-blue-600 hover:underline">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ===== CARD MOBILE ===== --}}
        <div class="md:hidden space-y-4 mt-4">
            @forelse ($data as $row)
                <div
                    class="rounded-3xl border border-gray-100 bg-white
                   hover:shadow-xl hover:-translate-y-0.5
                   transition-all duration-200
                   p-5">

                    <div class="flex justify-between items-start gap-4">

                        <!-- LEFT : DATA PENANGANAN -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-900 truncate">
                                {{ $row->siswa->nama }}
                            </h3>

                            <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                                <span class="font-medium">{{ ucfirst($row->jenis_penanganan) }}</span>
                                <span class="mx-1">•</span>
                                Petugas: {{ $row->petugas->name }}
                            </p>

                            <div
                                class="mt-2 inline-flex items-center gap-2
                                px-3 py-1 text-xs rounded-full font-semibold
                                {{ $row->status === 'selesai' ? 'bg-success text-white' : 'bg-yellow-300 text-gray-800' }} ">
                                {{ ucfirst($row->status) }}
                            </div>
                        </div>

                        <!-- RIGHT : ACTION -->
                        <div class="flex flex-col gap-2 shrink-0">
                            <a href="{{ route('penanganan.show', $row->siswa->id) }}"
                                class="flex items-center justify-center gap-2
                               px-3 py-2 text-sm
                               bg-gray-100 text-gray-700
                               rounded-xl
                               hover:bg-gray-200 transition">
                                <i class="fas fa-eye text-xs"></i>
                                Detail
                            </a>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-6">
                    Tidak ada data penanganan.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $data->links() }}
        </div>
    </div>
@endsection
