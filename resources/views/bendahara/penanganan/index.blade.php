@extends('layouts.dashboard')

@section('title', 'Penanganan Siswa')

@section('content')
    <div class="bg-white p-6 rounded-xl shadow">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-semibold text-gray-800">
                Daftar Siswa
            </h1>

            <form method="GET" class="flex items-center gap-2">
                <input type="text" name="search" placeholder="Cari nama / ID Person..." value="{{ request('search') }}"
                    class="px-3 py-2 border rounded-lg text-sm w-64 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Cari
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">ID</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Nama</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Gender</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Lembaga</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Asrama</th>
                        <th class="px-4 py-3 text-center text-gray-600 text-sm font-semibold">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($siswa as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $item->idperson }}</td>
                            <td class="px-4 py-3 font-medium">{{ $item->nama }}</td>
                            <td class="px-4 py-3">{{ $item->gender }}</td>
                            <td class="px-4 py-3">{{ $item->UnitFormal ?? '-' }} - {{ $item->KelasFormal ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $item->AsramaPondok ?? '-' }} - {{ $item->KamarPondok ?? '-' }}</td>

                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('bendahara.siswa.show', $item->id) }}"
                                    class="text-blue-600 hover:underline">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">
                                Tidak ada data siswa ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $siswa->links() }}
        </div>
    </div>
@endsection
