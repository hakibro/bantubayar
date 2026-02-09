@extends('layouts.dashboard')

@section('title', 'Daftar Siswa')

@push('styles')
    <style>
        /* Custom styles for loading and error modals */
        /* Custom scrollbar agar rapi */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Transisi halus untuk Mobile Filter */
        .filter-overlay {
            transition: opacity 0.3s ease-in-out;
            opacity: 0;
            pointer-events: none;
        }

        .filter-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .filter-drawer {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(100%);
        }

        .filter-drawer.open {
            transform: translateY(0);
        }
    </style>
@endpush
@section('content')

    <div class="bg-gray-100 p-6 rounded-xl shadow">
        <form method="GET" id="filterForm">
            <!-- FILTER CARD -->
            <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <!-- BAGIAN 1: HEADER (SEARCH & ACTIONS) -->
                <div class="p-4 md:p-6 border-b border-gray-100">
                    <div class="flex flex-col md:flex-row justify-between gap-4">
                        <!-- Search Input (Mengambil ruang penuh) -->
                        <div class="relative w-full md:w-1/3">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" name="search" placeholder="Cari nama / ID Person..."
                                class="w-full  pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                                value="{{ request('search') }}" />
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <!-- Tombol Filter Mobile (Hanya muncul di layar kecil) -->
                            <button type="button" onclick="toggleFilter()"
                                class="md:hidden flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg text-sm font-semibold hover:bg-blue-100 active:scale-95 transition whitespace-nowrap w-full md:w-auto">
                                <i class="fas fa-sliders-h"></i> Filter Data
                            </button>

                            <!-- Tombol Reset (Hanya Desktop) -->
                            <button type="button" onclick="resetForm()"
                                class="hidden md:block px-5 py-2.5 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition font-medium">
                                Reset
                            </button>

                            <!-- Tombol Terapkan (Hanya Desktop) -->
                            <button type="submit" onclick="syncDesktopToMobile()"
                                class="hidden md:flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 active:scale-95 transition shadow-md shadow-blue-200">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 2: DESKTOP FILTER GRID -->
                <div class="hidden md:block bg-gray-50 p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4 gap-y-6">
                        <!-- Group Formal -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                Sekolah Formal
                            </h4>
                            <div class="grid grid-cols-2">
                                <select name="UnitFormal"
                                    class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-r-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
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
                                    class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-l-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">Kelas...</option>
                                    <!-- Loop Blade -->
                                    @foreach ($filterOptions['KelasFormal'] as $item)
                                        <option value="{{ $item }}"
                                            {{ request('KelasFormal') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Group Pondok -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                Asrama Pondok
                            </h4>
                            <div class="grid grid-cols-2">
                                <select name="AsramaPondok"
                                    class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-r-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                    {{ $lock['AsramaPondok'] ? 'disabled' : '' }}>
                                    <option value="">Asrama...</option>
                                    @foreach ($filterOptions['AsramaPondok'] as $item)
                                        <option value="{{ $item }}"
                                            {{ request('AsramaPondok', $selected['AsramaPondok']) == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="KamarPondok"
                                    class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-l-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">Kamar...</option>
                                    @foreach ($filterOptions['KamarPondok'] as $item)
                                        <option value="{{ $item }}"
                                            {{ request('KamarPondok') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Group Diniyah -->
                        <div class="space-y-3">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                Diniyah
                            </h4>
                            <div class="grid grid-cols-2">
                                <select name="TingkatDiniyah"
                                    class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-r-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                    {{ $lock['TingkatDiniyah'] ? 'disabled' : '' }}>
                                    <option value="">Tingkat...</option>
                                    @foreach ($filterOptions['TingkatDiniyah'] as $item)
                                        <option value="{{ $item }}"
                                            {{ request('TingkatDiniyah', $selected['TingkatDiniyah']) == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                                <select name="KelasDiniyah"
                                    class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-l-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="">Kelas D...</option>
                                    @foreach ($filterOptions['KelasDiniyah'] as $item)
                                        <option value="{{ $item }}"
                                            {{ request('KelasDiniyah') == $item ? 'selected' : '' }}>
                                            {{ $item }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- MOBILE DRAWER -->
            <div id="filterOverlay" onclick="toggleFilter()"
                class="filter-overlay fixed inset-0 bg-black/50 z-40 xl:hidden">
            </div>

            <div id="filterDrawer"
                class="filter-drawer fixed bottom-0 left-0 right-0 bg-white z-50 rounded-t-2xl shadow-2xl xl:hidden flex flex-col max-h-[85vh]">
                <!-- Handle Bar -->
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                </div>

                <!-- Drawer Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">Filter</h3>
                    <button onclick="toggleFilter()" class="p-2 text-gray-500 hover:bg-gray-100 rounded-full">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Drawer Content (Scrollable) -->
                <div class="overflow-y-auto p-6 space-y-6 bg-gray-50">
                    <!-- Mobile: Group Formal -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider">
                            Sekolah Formal
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="UnitFormal"
                                class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                                {{ $lock['UnitFormal'] ? 'disabled' : '' }}>
                                <option value="">Lembaga...</option>
                                @foreach ($filterOptions['UnitFormal'] as $item)
                                    <option value="{{ $item }}"
                                        {{ request('UnitFormal', $selected['UnitFormal']) == $item ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="KelasFormal"
                                class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">Kelas...</option>
                                @foreach ($filterOptions['KelasFormal'] as $item)
                                    <option value="{{ $item }}"
                                        {{ request('KelasFormal') == $item ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Mobile: Group Pondok -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-green-600 uppercase tracking-wider">
                            Asrama Pondok
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="AsramaPondok" {{ $lock['AsramaPondok'] ? 'disabled' : '' }}
                                class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">Asrama...</option>
                                @foreach ($filterOptions['AsramaPondok'] as $item)
                                    <option value="{{ $item }}"
                                        {{ request('AsramaPondok', $selected['AsramaPondok']) == $item ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="KamarPondok"
                                class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">Kamar...</option>
                                @foreach ($filterOptions['KamarPondok'] as $item)
                                    <option value="{{ $item }}"
                                        {{ request('KamarPondok') == $item ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Mobile: Group Diniyah -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-amber-600 uppercase tracking-wider">
                            Diniyah
                        </h4>
                        <div class="grid grid-cols-2 gap-3">
                            <select name="TingkatDiniyah" {{ $lock['TingkatDiniyah'] ? 'disabled' : '' }}
                                class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">Tingkat...</option>
                                @foreach ($filterOptions['TingkatDiniyah'] as $item)
                                    <option value="{{ $item }}"
                                        {{ request('TingkatDiniyah', $selected['TingkatDiniyah']) == $item ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="KelasDiniyah"
                                class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">Kelas...</option>
                                @foreach ($filterOptions['KelasDiniyah'] as $item)
                                    <option value="{{ $item }}"
                                        {{ request('KelasDiniyah') == $item ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer -->
                <div class="p-4 border-t border-gray-200 bg-white grid grid-cols-4 gap-3">
                    <button type="button" onclick="resetForm()"
                        class="col-span-1 flex items-center justify-center text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition text-sm py-3">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" onclick="syncMobileToDesktopAndSubmit()"
                        class="col-span-3 flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 transition shadow-lg shadow-blue-200 font-semibold text-sm">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>

        {{-- DATA SISWA (RESPONSIVE GRID CARDS) --}}
        {{-- Grid Layout: 1 Kolom di Mobile, 2 di Tablet, 3 di Desktop, 4 di Layar Lebar --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mt-6">
            @forelse ($siswa as $item)
                <div
                    class="{{ $item->sedangDitangani() ? 'bg-blue-100 ring-2 ring-blue-200' : 'bg-white' }}
           rounded-2xl border border-gray-100 shadow-sm
           hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200
           p-5 flex flex-col h-full">

                    <div class="flex justify-between items-start gap-4 flex-1">
                        <!-- LEFT : DATA SISWA -->
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 text-lg leading-tight truncate">
                                {{ $item->nama }}
                            </h3>
                            <p class="mt-1 text-xs font-mono text-gray-500 bg-gray-100 inline-block px-1.5 py-0.5 rounded">
                                {{ $item->idperson }}
                            </p>

                            <div class="mt-3 space-y-1.5">
                                <!-- Formal -->
                                <div class="flex items-start text-xs text-gray-600">
                                    <i class="fas fa-school mt-0.5 w-4 text-blue-400"></i>
                                    <span class="truncate">
                                        {{ $item->UnitFormal ?? '-' }} - {{ $item->KelasFormal ?? '-' }}
                                    </span>
                                </div>
                                <!-- Pondok -->
                                <div class="flex items-start text-xs text-gray-600">
                                    <i class="fas fa-bed mt-0.5 w-4 text-green-400"></i>
                                    <span class="truncate">
                                        {{ $item->AsramaPondok ?? '-' }} - {{ $item->KamarPondok ?? '-' }}
                                    </span>
                                </div>
                                <!-- Diniyah -->
                                <div class="flex items-start text-xs text-gray-600">
                                    <i class="fas fa-mosque mt-0.5 w-4 text-amber-400"></i>
                                    <span class="truncate">
                                        {{ $item->TingkatDiniyah ?? '-' }} - {{ $item->KelasDiniyah ?? '-' }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-3 mb-1">
                                @include('petugas.siswa.partials.status-siswa')
                            </div>

                            @if ($item->sedangDitangani())
                                <div
                                    class="mt-2 inline-flex items-center gap-1.5
                            px-2.5 py-1 text-[10px] font-bold rounded-md
                            bg-blue-600 text-white shadow-sm shadow-blue-200">
                                    <i class="fas fa-user-check"></i>
                                    <span> Ditangani: {{ $item->petugasPenangananAktif() }}</span>
                                </div>
                            @endif

                        </div>
                    </div>

                    <!-- BOTTOM : ACTION BUTTONS -->
                    <div class="mt-4 pt-4 border-t border-gray-100/50 flex flex-col sm:flex-row gap-3">
                        <button onclick="syncPembayaran({{ $item->id }})"
                            class="flex items-center justify-center gap-2
                       px-3 py-2 text-xs font-bold uppercase tracking-wide
                       bg-blue-50 text-blue-600
                       rounded-lg
                       hover:bg-blue-100 transition">
                            <i class="fas fa-sync"></i> Sync
                        </button>

                        <a href="{{ route('penanganan.show', $item->id) }}"
                            class="flex items-center justify-center gap-2
                      px-3 py-2 text-xs font-bold uppercase tracking-wide
                      bg-gray-800 text-white
                      rounded-lg
                      hover:bg-gray-900 transition shadow-md shadow-gray-200">
                            <i class="fas fa-arrow-right"></i> Aksi
                        </a>
                    </div>
                </div>

            @empty
                <div
                    class="col-span-full flex flex-col items-center justify-center py-16 text-center bg-white rounded-2xl border border-dashed border-gray-300">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-gray-700">Tidak ada data siswa</h4>
                    <p class="text-gray-500 text-sm mt-1">Coba ubah kata kunci pencarian atau filter Anda.</p>
                </div>
            @endforelse
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8">
            {{ $siswa->links() }}
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        function toggleFilter() {
            const drawer = document.getElementById("filterDrawer");
            const overlay = document.getElementById("filterOverlay");
            const body = document.body;
            const isOpen = drawer.classList.contains("open");

            if (isOpen) {
                drawer.classList.remove("open");
                overlay.classList.remove("open");
                body.style.overflow = "";
            } else {
                // Sync values dari Desktop ke Mobile saat dibuka
                syncDesktopToMobile();

                drawer.classList.add("open");
                overlay.classList.add("open");
                body.style.overflow = "hidden";
            }
        }

        // Sinkronisasi nilai filter antara Tampilan Desktop dan Mobile Drawer
        function syncDesktopToMobile() {
            const mobileSelects = document.querySelectorAll(".mobile-select");
            mobileSelects.forEach((mSelect) => {
                const name = mSelect.name;
                const dSelect = document.querySelector(
                    `#filterForm select[name="${name}"]:not(.mobile-select)`
                );
                if (dSelect) mSelect.value = dSelect.value;
            });
        }

        function syncMobileToDesktopAndSubmit() {
            const mobileSelects = document.querySelectorAll(".mobile-select");
            mobileSelects.forEach((mSelect) => {
                const name = mSelect.name;
                const dSelect = document.querySelector(
                    `#filterForm select[name="${name}"]:not(.mobile-select)`
                );
                if (dSelect) dSelect.value = mSelect.value;
            });

            toggleFilter();
            document.getElementById("filterForm").submit();
        }

        function resetForm() {
            const form = document.getElementById("filterForm");
            // Reset semua select di dalam form
            const selects = form.querySelectorAll("select");
            selects.forEach((s) => (s.selectedIndex = 0));
            // Reset text input
            const textInput = form.querySelector('input[type="text"]');
            if (textInput) textInput.value = "";

            // Jika drawer terbuka, tutup dulu
            const drawer = document.getElementById("filterDrawer");
            if (drawer.classList.contains("open")) {
                toggleFilter();
            }

            document.getElementById("filterForm").submit();
        }
    </script>
@endpush
