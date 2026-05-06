@extends('layouts.container')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 flex justify-between items-center">
                <h1 class="text-white font-bold text-xl">Daftar Siswa (Periode < 20242025) - Tunggakan & Kelebihan Bayar</h1>
                        <a href="{{ route('siswa.belum-lunas.export', request()->query()) }}"
                            class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export XLSX
                        </a>
            </div>

            <div class="p-6">
                <!-- Form Filter -->
                <form method="GET" action="{{ route('siswa.belum-lunas.index') }}" class="mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Cari (Nama / ID)</label>
                            <input type="text" name="keyword" value="{{ request('keyword') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Unit Formal</label>
                            <select name="unit_formal" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Semua</option>
                                @foreach ($unitFormalList as $uf)
                                    <option value="{{ $uf }}"
                                        {{ request('unit_formal') == $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Asrama Pondok</label>
                            <select name="asrama_pondok" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Semua</option>
                                @foreach ($asramaPondokList as $ap)
                                    <option value="{{ $ap }}"
                                        {{ request('asrama_pondok') == $ap ? 'selected' : '' }}>{{ $ap }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium mb-1">Tingkat Diniyah</label>
                            <select name="tingkat_diniyah" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">Semua</option>
                                @foreach ($tingkatDiniyahList as $td)
                                    <option value="{{ $td }}"
                                        {{ request('tingkat_diniyah') == $td ? 'selected' : '' }}>{{ $td }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg">Filter</button>
                            <a href="{{ route('siswa.belum-lunas.index') }}"
                                class="bg-gray-400 hover:bg-gray-500 text-white font-medium py-2 px-4 rounded-lg text-center">Reset</a>
                        </div>
                    </div>
                </form>

                <!-- Tabel -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Person</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Formal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas Formal</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Asrama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tingkat Madin</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($siswaList as $siswa)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">{{ $siswa->idperson }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ $siswa->nama }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->unit_formal }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->kelas_formal }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->AsramaPondok }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $siswa->TingkatMadin }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('siswa.show', $siswa->idperson) }}"
                                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-lg">
                                            Lihat Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-6 text-gray-500">Tidak ada data siswa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $siswaList->appends(request()->query())->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </div>

@endsection
