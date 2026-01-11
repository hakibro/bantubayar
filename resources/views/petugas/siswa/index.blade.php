@extends('layouts.dashboard')

@section('title', 'Penanganan Siswa')

@section('content')
    <div class="bg-gray-100 p-6 rounded-xl shadow">
        <div class="flex items-center justify-between mb-4 md:hidden">
            <h1 class="text-xl font-semibold text-gray-800">
                Daftar Siswa
            </h1>

            <button type="button" onclick="toggleFilter()"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm shadow active:scale-95 transition">
                <i class="fas fa-filter"></i>
                Filter
            </button>
        </div>
        <h1 class="text-xl font-semibold text-gray-800 mb-4 hidden md:block">
            Daftar Siswa
        </h1>


        {{-- FILTER BAR --}}
        <form method="GET" id="filterBox"
            class="hidden md:flex flex-wrap items-center gap-3 mb-6
           bg-white p-4 rounded-xl shadow border border-gray-200
           md:static fixed inset-x-0 bottom-0 z-40
           md:rounded-xl rounded-t-3xl">

            {{-- Search --}}
            <input type="text" name="search" placeholder="Cari nama / ID Person..." value="{{ request('search') }}"
                class="px-3 py-2 border rounded-lg text-sm w-full md:w-48
               focus:ring-2 focus:ring-blue-500 focus:border-blue-500">


            {{-- FORMAL --}}
            <select name="UnitFormal"
                class="px-3 py-2 border rounded-lg text-sm bg-white
           w-full md:w-40 focus:ring-2 focus:ring-blue-500"
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
                class="px-3 py-2 border rounded-lg text-sm bg-white
           w-full md:w-40 focus:ring-2 focus:ring-blue-500">
                <option value="">Kelas</option>
                @foreach ($filterOptions['KelasFormal'] as $item)
                    <option value="{{ $item }}" {{ request('KelasFormal') == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>


            {{-- PONDOK --}}
            <select name="AsramaPondok"
                class="px-3 py-2 border rounded-lg text-sm bg-white
           w-full md:w-40 focus:ring-2 focus:ring-blue-500"
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
                class="px-3 py-2 border rounded-lg text-sm bg-white
           w-full md:w-40 focus:ring-2 focus:ring-blue-500">
                <option value="">Kamar</option>
                @foreach ($filterOptions['KamarPondok'] as $item)
                    <option value="{{ $item }}" {{ request('KamarPondok') == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>


            {{-- DINIYAH --}}
            <select name="TingkatDiniyah"
                class="px-3 py-2 border rounded-lg text-sm bg-white
           w-full md:w-40 focus:ring-2 focus:ring-blue-500"
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
                class="px-3 py-2 border rounded-lg text-sm bg-white
           w-full md:w-40 focus:ring-2 focus:ring-blue-500">
                <option value="">Kelas Diniyah</option>
                @foreach ($filterOptions['KelasDiniyah'] as $item)
                    <option value="{{ $item }}" {{ request('KelasDiniyah') == $item ? 'selected' : '' }}>
                        {{ $item }}
                    </option>
                @endforeach
            </select>

            {{-- Tombol --}}
            <div class="w-full flex gap-2 mt-2">
                <button
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg
               hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
                    Terapkan Filter
                </button>

                <button type="button" onclick="toggleFilter()"
                    class="md:hidden flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">
                    Tutup
                </button>
            </div>

        </form>


        {{-- TABLE --}}
        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block overflow-x-auto">
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
                                    <span class="font-medium text-gray-900">{{ $item->nama }}</span>

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

                            <td class="px-4 py-3">
                                {{ $item->TingkatDiniyah ?? '-' }} - {{ $item->KelasDiniyah ?? '-' }}
                            </td>

                            <td class="px-4 py-3">
                                @php $belumLunas = $item->getKategoriBelumLunas(); @endphp
                                @include('petugas.siswa.partials.status-siswa')
                            </td>

                            <td class="px-4 py-3 text-center space-x-3">
                                <button onclick="syncPembayaran({{ $item->id }})"
                                    class="text-blue-600 hover:underline">
                                    <i class="fas fa-sync"></i> Sync
                                </button>

                                <a href="{{ route('penanganan.siswa', $item->id) }}" class="text-blue-600 hover:underline">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-gray-500">
                                Tidak ada data siswa ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARD --}}
        <div class="md:hidden space-y-4">
            @forelse ($siswa as $item)
                <div
                    class="{{ $item->sedangDitangani() ? 'bg-yellow-100' : 'bg-white' }}
           rounded-3xl border border-gray-100
           hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200
           p-5">


                    <div class="flex justify-between items-start gap-4">

                        <!-- LEFT : DATA SISWA -->
                        <div class="flex-1 min-w-0">
                            @php $belumLunas = $item->getKategoriBelumLunas(); @endphp

                            <h3 class="font-semibold text-gray-900 truncate">
                                {{ $item->nama }}
                            </h3>

                            <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                                <span class="font-medium">{{ $item->idperson }}</span>
                                <span class="mx-1">•</span>

                                {{ $item->UnitFormal ?? '-' }} - {{ $item->KelasFormal ?? '-' }}
                                <span class="mx-1">•</span>

                                {{ $item->AsramaPondok ?? '-' }} - {{ $item->KamarPondok ?? '-' }}
                                <span class="mx-1">•</span>

                                {{ $item->TingkatDiniyah ?? '-' }} - {{ $item->KelasDiniyah ?? '-' }}
                            </p>

                            <div class="mt-2">
                                @include('petugas.siswa.partials.status-siswa')
                            </div>
                            @if ($item->sedangDitangani())
                                <div
                                    class="mt-2 inline-flex items-center gap-2
                            px-3 py-1 text-xs rounded-full
                            bg-blue-50 text-blue-600">
                                    <span class="ml-1"> Ditangani oleh {{ $item->petugasPenangananAktif() }}
                                    </span>
                                </div>
                            @endif

                        </div>

                        <!-- RIGHT : ACTION -->
                        <div class="flex flex-col gap-2 shrink-0">
                            <button onclick="syncPembayaran({{ $item->id }})"
                                class="flex items-center justify-center gap-2
                       px-3 py-2 text-sm font-medium
                       bg-blue-50 text-blue-600
                       rounded-xl
                       hover:bg-blue-100 transition">
                                <i class="fas fa-sync text-xs"></i>
                                Sync
                            </button>

                            <a href="{{ route('penanganan.siswa', $item->id) }}"
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
                    Tidak ada data siswa ditemukan.
                </div>
            @endforelse
        </div>


        {{-- PAGINATION --}}
        <div class="mt-4">
            {{ $siswa->links() }}
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
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('select').forEach(function(el) {
            el.addEventListener('change', function() {
                this.form.submit();
            });
        });

        function toggleFilter() {
            const box = document.getElementById('filterBox');
            box.classList.toggle('hidden');
        }

        function syncPembayaran(id) {
            console.log(id);

            document.getElementById("loadingModal").classList.remove("hidden");

            fetch("{{ url('petugas/siswa/sync-pembayaran-siswa') }}/" + id, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(res => res.json())
                .then(data => {

                    if (!data.status) throw new Error(data.message);
                })
                .catch(err => {
                    document.getElementById("errorMessage").innerText = err.message || 'Gagal sync';
                    document.getElementById("errorModal").classList.remove("hidden");
                })
                .finally(() => {
                    document.getElementById("loadingModal").classList.add("hidden");
                    // Reload halaman setelah sync
                    location.reload();
                });
        }

        function closeError() {
            document.getElementById("errorModal").classList.add("hidden");
        }
    </script>
@endpush
