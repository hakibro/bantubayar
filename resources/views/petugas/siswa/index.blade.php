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

        /* Gaya default sudah ada di HTML (text-gray-400, border-gray-200) */

        #resetButton.active {
            background-color: #fef2f2;
            /* red-50 */
            border-color: #fecaca;
            /* red-200 */
            color: #ef4444;
            /* red-500 */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        #resetButton.active i {
            transform: rotate(-45deg);
            /* Efek sedikit putar agar lebih dinamis */
            transition: transform 0.3s ease;
        }
    </style>
@endpush
@section('content')

    <div class="bg-gray-100 p-6 rounded-xl shadow">
        <form method="GET" id="filterForm" class="sticky top-0 z-20">
            <div
                class="max-w-7xl mx-auto bg-white border border-blue-100 rounded-2xl shadow-xl shadow-blue-900/5 overflow-hidden transition-all duration-300">

                <div class="p-4 bg-white">
                    <div class="flex items-center gap-2">
                        <div class="relative flex-grow">
                            <input type="text" name="search" placeholder="Cari nama atau ID Yayasan..."
                                class="w-full px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm text-gray-700 placeholder-gray-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition-all outline-none"
                                value="{{ request('search') }}" />
                        </div>

                        <div class="flex items-center gap-1.5 md:gap-2">
                            <button id="filterButton" type="button" onclick="toggleFilter()"
                                class="md:hidden flex items-center justify-center w-11 h-11 bg-slate-100 text-gray-600 border border-gray-200 rounded-xl hover:bg-blue-600 hover:text-white transition-all">
                                <i class="fas fa-sliders-h"></i>
                            </button>

                            <button id="resetButton" type="button" onclick="window.location.href=window.location.pathname"
                                class="flex items-center justify-center h-11 px-3 md:px-4 text-sm text-gray-500 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl transition-all font-medium">
                                <i class="fas fa-undo md:mr-2"></i>
                                <span class="hidden md:inline">Reset</span>
                            </button>

                            <button type="submit"
                                class="flex items-center justify-center h-11 px-5 md:px-6 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700 active:scale-95 transition-all shadow-lg shadow-blue-200">
                                <i class="fas fa-search md:mr-2"></i>
                                <span class="hidden md:inline">Cari</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="filterSection" class="hidden md:block bg-slate-50/50 border-t border-gray-100">
                    <div class="p-4 md:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-[10px] font-bold text-purple-600 uppercase tracking-widest w-16 shrink-0">
                                    Status</h4>
                                <div
                                    class="flex flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-purple-400 transition-colors">
                                    <select name="status_penanganan"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none border-r border-gray-100">
                                        <option value="">Penanganan</option>
                                        <option value="belum_ditangani"
                                            {{ request('status_penanganan') == 'belum_ditangani' ? 'selected' : '' }}>Belum
                                            Ditangani
                                        </option>
                                        @foreach ($filterOptions['status_penanganan'] as $status)
                                            <option value="{{ $status }}"
                                                {{ request('status_penanganan') == $status ? 'selected' : '' }}>
                                                {{ Str::title(str_replace('_', ' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                    <select name="pembayaran_status"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none">
                                        <option value="">Pembayaran</option>
                                        <option value="lunas"
                                            {{ request('pembayaran_status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                        <option value="belum_lunas"
                                            {{ request('pembayaran_status') == 'belum_lunas' ? 'selected' : '' }}>Belum
                                            Lunas
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-[10px] font-bold text-blue-600 uppercase tracking-widest w-16 shrink-0">
                                    Formal</h4>
                                <div
                                    class="flex flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-blue-400 transition-colors">
                                    <select name="UnitFormal"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none border-r border-gray-100"
                                        {{ $lock['UnitFormal'] ? 'disabled' : '' }}>
                                        <option value="">Lembaga</option>
                                        @foreach ($filterOptions['UnitFormal'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('UnitFormal', $selected['UnitFormal']) == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <select name="KelasFormal"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none">
                                        <option value="">Kelas</option>
                                        @foreach ($filterOptions['KelasFormal'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('KelasFormal') == $item ? 'selected' : '' }}>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest w-16 shrink-0">
                                    Pondok</h4>
                                <div
                                    class="flex flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-emerald-400 transition-colors">
                                    <select name="AsramaPondok"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none border-r border-gray-100"
                                        {{ $lock['AsramaPondok'] ? 'disabled' : '' }}>
                                        <option value="">Asrama</option>
                                        @foreach ($filterOptions['AsramaPondok'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('AsramaPondok', $selected['AsramaPondok']) == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <select name="KamarPondok"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none">
                                        <option value="">Kamar</option>
                                        @foreach ($filterOptions['KamarPondok'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('KamarPondok') == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center justify-between gap-3">
                                <h4 class="text-[10px] font-bold text-amber-600 uppercase tracking-widest w-16 shrink-0">
                                    Diniyah</h4>
                                <div
                                    class="flex flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-amber-400 transition-colors">
                                    <select name="TingkatDiniyah"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none border-r border-gray-100"
                                        {{ $lock['TingkatDiniyah'] ? 'disabled' : '' }}>
                                        <option value="">Tingkat</option>
                                        @foreach ($filterOptions['TingkatDiniyah'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('TingkatDiniyah', $selected['TingkatDiniyah']) == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <select name="KelasDiniyah"
                                        class="w-1/2 px-2 py-2.5 bg-transparent text-gray-700 text-xs focus:outline-none">
                                        <option value="">Kelas</option>
                                        @foreach ($filterOptions['KelasDiniyah'] as $item)
                                            <option value="{{ $item }}"
                                                {{ request('KelasDiniyah') == $item ? 'selected' : '' }}>
                                                {{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="filterOverlay" class="fixed inset-0 bg-black/50 z-10 hidden transition-opacity duration-300"></div>

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
        document.addEventListener('DOMContentLoaded', function() {
            const filterForm = document.getElementById('filterForm');
            const container = document.getElementById('siswa-container');
            const paginationContainer = document.querySelector('.mt-8'); // Div pembungkus $siswa->links()
            let typingTimer;

            // Fungsi Utama Fetch Data
            function fetchSiswa(url = null) {
                // Indikator Loading
                container.style.opacity = '0.5';

                // Jika url kosong (berarti dari filter), bangun URL dari form
                if (!url) {
                    const formData = new FormData(filterForm);
                    const params = new URLSearchParams(formData).toString();
                    url = `${window.location.pathname}?${params}`;
                }

                fetch(url, {
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        container.innerHTML = data.html;
                        if (paginationContainer) {
                            paginationContainer.innerHTML = data.pagination;
                        }
                        container.style.opacity = '1';

                        // Update URL di browser tanpa reload
                        window.history.pushState({}, '', url);
                    })
                    .catch(err => {
                        console.error(err);
                        container.style.opacity = '1';
                    });
            }

            // 1. Event Dropdown (Live Search)
            filterForm.querySelectorAll('select').forEach(select => {
                select.addEventListener('change', () => fetchSiswa());
            });

            // 2. Event Input Search (Debounce 500ms)
            const searchInput = filterForm.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(() => fetchSiswa(), 500);
                });
            }

            // 3. Event Form Submit (Mencegah reload)
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetchSiswa();
            });

            // 4. Handle Klik Pagination (Agar tidak reload halaman)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.pagination a')) {
                    e.preventDefault();
                    const url = e.target.closest('.pagination a').href;
                    fetchSiswa(url);
                    // Scroll ke atas form agar user tahu data berubah
                    filterForm.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Toggle Filter Mobile (Tetap sama)
        function toggleFilter() {
            const section = document.getElementById('filterSection');
            const btn = document.getElementById('filterButton');
            const overlay = document.getElementById('filterOverlay');
            const icon = btn.querySelector('i');

            if (window.innerWidth < 768) {
                const isHidden = section.classList.toggle('hidden');

                // Toggle Overlay
                overlay.classList.toggle('hidden');

                // Toggle Icon & Warna Tombol
                icon.classList.toggle('fa-sliders-h');
                icon.classList.toggle('fa-times');
                btn.classList.toggle('bg-slate-100');
                btn.classList.toggle('bg-gray-900');
                btn.classList.toggle('text-white');

                // Mencegah scroll pada body saat filter terbuka
                document.body.style.overflow = isHidden ? '' : 'hidden';
            }
        }

        // Tutup jika klik di area overlay
        document.getElementById('filterOverlay').addEventListener('click', function() {
            toggleFilter();
        });

        // Tetap pertahankan click outside untuk keamanan tambahan
        document.addEventListener('click', function(event) {
            const section = document.getElementById('filterSection');
            const btn = document.getElementById('filterButton');

            if (window.innerWidth < 768 &&
                !section.classList.contains('hidden') &&
                !section.contains(event.target) &&
                !btn.contains(event.target)) {
                toggleFilter();
            }
        });

        function checkFilterActive() {
            const form = document.getElementById('filterForm');
            const resetBtn = document.getElementById('resetButton');

            // Ambil semua input/select, lalu filter yang tidak disabled
            const activeInputs = Array.from(form.querySelectorAll('input[type="text"], select'))
                .filter(input => !input.disabled);

            // Cek apakah ada input aktif yang memiliki nilai
            const isAnyFilled = activeInputs.some(input => input.value !== "");

            if (isAnyFilled) {
                resetBtn.classList.add('active');
            } else {
                resetBtn.classList.remove('active');
            }
        }

        // Pantau perubahan di form
        document.getElementById('filterForm').addEventListener('input', checkFilterActive);
        document.getElementById('filterForm').addEventListener('change', checkFilterActive);

        // Cek saat halaman pertama kali dibuka
        window.addEventListener('load', checkFilterActive);
    </script>
@endpush
