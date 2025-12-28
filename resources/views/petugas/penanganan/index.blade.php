@extends('layouts.dashboard')

@section('title', 'Penanganan Siswa')

@section('content')
    <div class="p-6">

        <h1 class="text-2xl font-bold mb-5">Daftar Siswa yang Ditangani</h1>

        <div class="bg-white shadow rounded-lg p-4">

            <table class="w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 text-left">Nama</th>
                        <th class="px-4 py-2 text-left">Lembaga Formal</th>
                        <th class="px-4 py-2 text-left">Pondok</th>
                        <th class="px-4 py-2 text-left">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($siswaDitangani as $item)
                        <tr class="border-b">
                            <td class="px-4 py-2">{{ $item->siswa->nama }}</td>
                            <td class="px-4 py-2">{{ $item->siswa->UnitFormal }} - {{ $item->siswa->KelasFormal }}</td>
                            <td class="px-4 py-2">{{ $item->siswa->AsramaPondok }} - {{ $item->siswa->KamarPondok }}</td>
                            <td class="px-4 py-2">
                                <a href="
                                {{ route('penanganan.siswa', $item->siswa->id) }}
                                 "
                                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center p-4 text-gray-500">
                                Belum ada siswa yang ditangani.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    </div>
@endsection
