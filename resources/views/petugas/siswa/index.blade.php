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
        <form method="GET" id="filterForm" class="sticky top-0 z-10">
            <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

                <div class="p-4 md:p-6 border-b border-gray-100">
                    <div class="flex items-center gap-2 md:gap-4">

                        <div class="relative flex-grow">

                            <input type="text" name="search" placeholder="Cari nama / ID..."
                                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                value="{{ request('search') }}" />
                        </div>

                        <div class="flex items-center gap-2">
                            <button id="filterButton" type="button" onclick="toggleFilter()"
                                class="md:hidden flex items-center justify-center w-10 h-10 bg-gray-100 text-gray-600 border border-gray-200 rounded-lg">
                                <i class="fas fa-sliders-h"></i>
                            </button>

                            <button type="button" onclick="window.location.href=window.location.pathname"
                                class="flex items-center justify-center h-10 md:w-auto px-3 md:px-5 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition font-medium border border-gray-200"
                                title="Reset Filter">
                                <i class="fas fa-undo md:mr-2"></i>
                                <span class="hidden md:inline">Reset</span>
                            </button>

                            <button type="submit"
                                class="flex items-center justify-center h-10 md:w-auto px-4 md:px-6 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-md shadow-blue-200"
                                title="Cari">
                                <i class="fas fa-search md:mr-2"></i>
                                <span class="hidden md:inline">Cari</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="filterSection" class="hidden md:block bg-gray-50">
                    <div class="p-4 md:p-6 border-t border-gray-100 md:border-t-0">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider">
                                    Sekolah Formal</h4>
                                <div class="flex md:grid md:grid-cols-2">
                                    <select name="UnitFormal"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 rounded-l-lg text-sm focus:ring-2 focus:ring-blue-500"
                                        {{ $lock['UnitFormal'] ? 'disabled' : '' }}>
                                        <option value="">Lembaga</option>
                                        @foreach ($filterOptions['UnitFormal'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('UnitFormal', $selected['UnitFormal']) == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <select name="KelasFormal"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 border-l-0 rounded-r-lg text-sm focus:ring-2 focus:ring-blue-500">
                                        <option value="">Kelas...</option>
                                        @foreach ($filterOptions['KelasFormal'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('KelasFormal') == $item ? 'selected' : '' }}>{{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-green-600 uppercase tracking-wider">
                                    Asrama Pondok</h4>
                                <div class="flex md:grid md:grid-cols-2">
                                    <select name="AsramaPondok"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 rounded-l-lg text-sm focus:ring-2 focus:ring-blue-500"
                                        {{ $lock['AsramaPondok'] ? 'disabled' : '' }}>
                                        <option value="">Asrama...</option>
                                        @foreach ($filterOptions['AsramaPondok'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('AsramaPondok', $selected['AsramaPondok']) == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <select name="KamarPondok"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 border-l-0 rounded-r-lg text-sm focus:ring-2 focus:ring-blue-500">
                                        <option value="">Kamar...</option>
                                        @foreach ($filterOptions['KamarPondok'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('KamarPondok') == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-amber-600 uppercase tracking-wider">
                                    Diniyah</h4>
                                <div class="flex md:grid md:grid-cols-2">
                                    <select name="TingkatDiniyah"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 rounded-l-lg text-sm focus:ring-2 focus:ring-blue-500"
                                        {{ $lock['TingkatDiniyah'] ? 'disabled' : '' }}>
                                        <option value="">Tingkat...</option>
                                        @foreach ($filterOptions['TingkatDiniyah'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('TingkatDiniyah', $selected['TingkatDiniyah']) == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <select name="KelasDiniyah"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 border-l-0 rounded-r-lg text-sm focus:ring-2 focus:ring-blue-500">
                                        <option value="">Kelas...</option>
                                        @foreach ($filterOptions['KelasDiniyah'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('KelasDiniyah') == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-amber-600 uppercase tracking-wider">
                                    Penanganan</h4>
                                <div class="flex md:grid md:grid-cols-2">
                                    <select name="status_penanganan"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 rounded-l-lg text-sm focus:ring-2 focus:ring-blue-500">
                                        <option value="">Status...</option>
                                        <option value="belum_ditangani">Belum Ditangani</option>
                                        @foreach ($filterOptions['status_penanganan'] as $status)
                                            <option value="{{ $status }}"
                                                {{ request('status_penanganan') == $status ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <select name="hasil_penanganan"
                                        class="w-full px-2 py-2 bg-white border border-gray-200 border-l-0 rounded-r-lg text-sm focus:ring-2 focus:ring-blue-500">
                                        <option value="">Hasil...</option>
                                        @foreach ($filterOptions['hasil_penanganan'] as $hasil)
                                            <option value="{{ $hasil }}"
                                                {{ request('hasil_penanganan') == $hasil ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $hasil)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </form>

        {{-- DATA SISWA (RESPONSIVE GRID CARDS) --}}
        {{-- Grid Layout: 1 Kolom di Mobile, 2 di Tablet, 3 di Desktop, 4 di Layar Lebar --}}
        <div id="siswa-container" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mt-6">
            @include('petugas.siswa.partials.list-siswa')
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
            const filterSection = document.getElementById('filterSection');
            const filterButton = document.getElementById('filterButton');
            // Toggle class hidden pada mobile saja
            if (window.innerWidth < 768) {
                filterSection.classList.toggle('hidden');
                filterButton.classList.toggle('bg-gray-100');
                filterButton.classList.toggle('bg-blue-100');
            }
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

        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const selects = filterForm.querySelectorAll('select');
            const searchInput = filterForm.querySelector('input[name="search"]');
            const container = document.getElementById('siswa-container');
            let typingTimer;

            // 1. Dropdown tetap submit form (reload halaman)
            selects.forEach(select => {
                select.addEventListener('change', () => filterForm.submit());
            });

            // 2. Input Search menggunakan AJAX
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);

                    // Indikator loading visual sederhana
                    container.style.opacity = '0.5';

                    typingTimer = setTimeout(() => {
                        const formData = new FormData(filterForm);
                        const params = new URLSearchParams(formData).toString();

                        fetch(`${window.location.pathname}?${params}`, {
                                headers: {
                                    "X-Requested-With": "XMLHttpRequest"
                                }
                            })
                            .then(res => res.text())
                            .then(html => {
                                container.innerHTML = html;
                                container.style.opacity = '1';

                                // Update URL browser tanpa reload (opsional)
                                window.history.pushState({}, '', `?${params}`);
                            })
                            .catch(err => {
                                console.error(err);
                                container.style.opacity = '1';
                            });
                    }, 500); // 500ms lebih cepat untuk live search
                });
            }
        });
    </script>
@endpush
