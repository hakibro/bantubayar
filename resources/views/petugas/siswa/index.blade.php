@extends('layouts.dashboard')

@section('title', 'Penanganan Siswa')

@section('content')
    <div class="bg-white p-6 rounded-xl shadow">
        <h1 class="text-xl font-semibold text-gray-800 mb-4">
            Daftar Siswa
        </h1>

        {{-- FILTER BAR --}}
        <form method="GET"
            class="flex flex-wrap items-center gap-3 mb-6 bg-white p-4 rounded-xl shadow border border-gray-200">

            {{-- Search --}}
            <input type="text" name="search" placeholder="Cari nama / ID Person..." value="{{ request('search') }}"
                class="px-3 py-2 border rounded-lg text-sm w-48 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">


            {{-- FORMAL --}}
            <select name="UnitFormal"
                class="px-3 py-2 border rounded-lg text-sm bg-white w-40 focus:ring-2 focus:ring-blue-500"
                {{ $lock['UnitFormal'] ? 'disabled' : '' }}>
                <option value="">Lembaga</option>
                @foreach ($filterOptions['UnitFormal'] as $item)
                    <option value="{{ $item }}"
                        {{ request('UnitFormal', $selected['UnitFormal']) == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>

            <select name="KelasFormal"
                class="px-3 py-2 border rounded-lg text-sm bg-white w-32 focus:ring-2 focus:ring-blue-500">
                <option value="">Kelas</option>
                @foreach ($filterOptions['KelasFormal'] as $item)
                    <option value="{{ $item }}" {{ request('KelasFormal') == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>


            {{-- PONDOK --}}
            <select name="AsramaPondok"
                class="px-3 py-2 border rounded-lg text-sm bg-white w-36 focus:ring-2 focus:ring-blue-500"
                {{ $lock['AsramaPondok'] ? 'disabled' : '' }}>
                <option value="">Asrama</option>
                @foreach ($filterOptions['AsramaPondok'] as $item)
                    <option value="{{ $item }}"
                        {{ request('AsramaPondok', $selected['AsramaPondok']) == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>

            <select name="KamarPondok"
                class="px-3 py-2 border rounded-lg text-sm bg-white w-32 focus:ring-2 focus:ring-blue-500">
                <option value="">Kamar</option>
                @foreach ($filterOptions['KamarPondok'] as $item)
                    <option value="{{ $item }}" {{ request('KamarPondok') == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>


            {{-- DINIYAH --}}
            <select name="TingkatDiniyah"
                class="px-3 py-2 border rounded-lg text-sm bg-white w-36 focus:ring-2 focus:ring-blue-500"
                {{ $lock['TingkatDiniyah'] ? 'disabled' : '' }}>
                <option value="">Diniyah</option>
                @foreach ($filterOptions['TingkatDiniyah'] as $item)
                    <option value="{{ $item }}"
                        {{ request('TingkatDiniyah', $selected['TingkatDiniyah']) == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>

            <select name="KelasDiniyah"
                class="px-3 py-2 border rounded-lg text-sm bg-white w-36 focus:ring-2 focus:ring-blue-500">
                <option value="">Kelas Diniyah</option>
                @foreach ($filterOptions['KelasDiniyah'] as $item)
                    <option value="{{ $item }}" {{ request('KelasDiniyah') == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>

            {{-- Tombol --}}
            <button
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                Filter
            </button>
        </form>
        <script>
            document.querySelectorAll('select').forEach(function(el) {
                el.addEventListener('change', function() {
                    this.form.submit();
                });
            });
        </script>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">ID</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Nama</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Lembaga</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Asrama</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Diniyah</th>
                        <th class="px-4 py-3 text-left text-gray-600 text-sm font-semibold">Status</th>
                        <th class="px-4 py-3 text-center text-gray-600 text-sm font-semibold">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($siswa as $item)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $item->idperson }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="font-medium text-gray-900">
                                        {{ $item->nama }}
                                    </span>

                                    @if ($item->sedangDitangani())
                                        <span class="text-xs text-blue-600">
                                            Sedang ditangani oleh {{ $item->petugasPenangananAktif() }}
                                        </span>
                                    @endif

                                </div>
                            </td>



                            <td class="px-4 py-3">
                                {{ $item->UnitFormal ?? '-' }} - {{ $item->KelasFormal ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $item->AsramaPondok ?? '-' }} - {{ $item->KamarPondok ?? '-' }}
                            </td>
                            <td class="px-4 py-3">{{ $item->TingkatDiniyah ?? '-' }} - {{ $item->KelasDiniyah ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $belumLunas = $item->getKategoriBelumLunas();
                                @endphp

                                @if (is_null($belumLunas))
                                    <span class="text-yellow-500 px-2 py-1 rounded">
                                        Belum Sinkron
                                    </span>
                                @elseif (count($belumLunas) > 0)
                                    <span class="text-red-500 px-2 py-1 rounded">
                                        Belum Lunas
                                    </span>
                                @else
                                    <span class="text-green-500 px-2 py-1 rounded">
                                        Lunas
                                    </span>
                                @endif

                            </td>

                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('petugas.siswa.show', $item->id) }}"
                                    class="text-blue-600 hover:underline">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-gray-500">
                                Tidak ada data siswa ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $siswa->links() }}
        </div>
    </div>
@endsection
