@extends('layouts.dashboard')

@section('title', 'Monitoring Siswa')

@push('styles')
    <style>
        .clean-bg {
            background: linear-gradient(135deg, rgba(224, 242, 254, .85), rgba(240, 253, 250, .55) 38%, #f8fafc 72%);
        }

        .clean-card {
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(226, 232, 240, .9);
            box-shadow: 0 14px 34px rgba(15, 23, 42, .06);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush

@section('content')
    @php
        $statusPenangananOptions = [
            '' => ['label' => 'Semua Siswa', 'count' => $statPenanganan['semua'] ?? 0, 'tone' => 'slate'],
            'sudah_ditangani' => [
                'label' => 'Sudah Ditangani',
                'count' => $statPenanganan['sudah_ditangani'] ?? 0,
                'tone' => 'indigo',
            ],
            'belum_ditangani' => [
                'label' => 'Belum Ditangani',
                'count' => $statPenanganan['belum_ditangani'] ?? 0,
                'tone' => 'rose',
            ],
            'aktif' => ['label' => 'Sedang Aktif', 'count' => $statPenanganan['aktif'] ?? 0, 'tone' => 'amber'],
            'selesai' => ['label' => 'Selesai', 'count' => $statPenanganan['selesai'] ?? 0, 'tone' => 'emerald'],
        ];
        $periodeOptions = [
            'minggu_ini' => 'Minggu Ini',
            'minggu_lalu' => 'Minggu Lalu',
            'bulan_ini' => 'Bulan Ini',
            'sebelumnya' => 'Sebelumnya',
            '' => 'Semua',
        ];
        $tagihanOptions = [
            '' => 'Semua Tagihan',
            '0' => 'Rp 0',
            '1_500k' => 'Rp 1 - 500rb',
            '500k_1jt' => '500rb - 1jt',
            '1jt_2jt' => '1jt - 2jt',
            '2jt_plus' => '> 2jt',
        ];
        $selectedStatusPenanganan = request('status_penanganan', '');
        $selectedPeriodePenanganan = request('periode_penanganan', 'bulan_ini');
        $selectedTagihan = request('tagihan_range', '');
        $selectedLembaga = request('lembaga_filter', '');
        $selectedSort = request('sort', '');
    @endphp

    <div class="clean-bg min-h-screen px-4 py-5 md:p-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-500">Siswa</p>
                    <h1 class="mt-1 text-2xl font-black text-slate-800 md:text-3xl">
                        {{ auth()->user()?->hasRole('monitoring') ? 'Monitoring Siswa' : 'Manage Siswa' }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">Pantau lembaga, penanganan, petugas, dan tagihan siswa.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a id="downloadSiswaLink" href="{{ route('admin.siswa.export', request()->query()) }}"
                        class="inline-flex rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">
                        <i class="fas fa-download mr-2"></i> Download Data Siswa
                    </a>
                @role('admin')
                    <a href="{{ route('admin.assign.index') }}"
                        class="hidden rounded-2xl bg-teal-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-teal-700 md:inline-flex">
                        <i class="fas fa-check mr-2"></i> Assign Siswa
                    </a>
                @endrole
                </div>
            </div>

            <section class="clean-card mb-5 rounded-3xl p-4">
                <div class="flex items-center gap-2">
                    <div class="relative grow">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input id="searchInput" type="text" value="{{ request('search') }}"
                            placeholder="Cari nama atau idperson..."
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-indigo-300 focus:ring-2 focus:ring-indigo-400/30">
                    </div>
                    <button id="resetButton" type="button"
                        class="flex h-12 items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-500 shadow-sm hover:border-red-200 hover:text-red-500">
                        <i class="fas fa-undo md:mr-2"></i>
                        <span class="hidden md:inline">Reset</span>
                    </button>
                </div>

                <input id="filterLembaga" type="hidden" value="{{ $selectedLembaga }}">
                <input id="filterStatusPenanganan" type="hidden" value="{{ $selectedStatusPenanganan }}">
                <input id="filterPeriodePenanganan" type="hidden" value="{{ $selectedPeriodePenanganan }}">
                <input id="filterTagihan" type="hidden" value="{{ $selectedTagihan }}">
                <input id="filterSort" type="hidden" value="{{ $selectedSort }}">

                <div class="mt-4">
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Lembaga</p>
                        <button id="clearLembagaButton" type="button"
                            class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Semua</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="block">
                            <span
                                class="mb-1 block text-[10px] font-black uppercase tracking-widest text-blue-500">Formal</span>
                            <select id="filterLembagaFormal"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-xs font-semibold text-slate-700 focus:border-blue-300 focus:ring-2 focus:ring-blue-200">
                                <option value="">Semua Formal</option>
                                @foreach ($daftarLembaga['formal'] as $lembaga)
                                    <option value="formal:{{ $lembaga }}"
                                        {{ $selectedLembaga === "formal:{$lembaga}" ? 'selected' : '' }}>{{ $lembaga }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span
                                class="mb-1 block text-[10px] font-black uppercase tracking-widest text-emerald-500">Pondok</span>
                            <select id="filterLembagaPondok"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-xs font-semibold text-slate-700 focus:border-emerald-300 focus:ring-2 focus:ring-emerald-200">
                                <option value="">Semua Pondok</option>
                                @foreach ($daftarLembaga['pondok'] as $lembaga)
                                    <option value="pondok:{{ $lembaga }}"
                                        {{ $selectedLembaga === "pondok:{$lembaga}" ? 'selected' : '' }}>
                                        {{ $lembaga }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span
                                class="mb-1 block text-[10px] font-black uppercase tracking-widest text-amber-500">Diniyah</span>
                            <select id="filterLembagaDiniyah"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-xs font-semibold text-slate-700 focus:border-amber-300 focus:ring-2 focus:ring-amber-200">
                                <option value="">Semua Diniyah</option>
                                @foreach ($daftarLembaga['diniyah'] as $lembaga)
                                    <option value="diniyah:{{ $lembaga }}"
                                        {{ $selectedLembaga === "diniyah:{$lembaga}" ? 'selected' : '' }}>
                                        {{ $lembaga }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>
            </section>

            <section class="clean-card mb-5 rounded-3xl p-4">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-bold tracking-normal text-gray-400">Periode & Status
                            Penanganan</p>
                    </div>
                    <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
                        @foreach ($periodeOptions as $value => $label)
                            <button type="button" data-periode-penanganan="{{ $value }}"
                                class="periodePenangananBtn shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition {{ $selectedPeriodePenanganan === $value ? 'border-indigo-500 bg-indigo-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                    @foreach ($statusPenangananOptions as $value => $option)
                        <button type="button" data-status-penanganan="{{ $value }}"
                            class="statusPenangananBtn rounded-2xl border p-4 text-left transition active:scale-[0.98] {{ $selectedStatusPenanganan === $value ? 'border-indigo-500 bg-indigo-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-700 hover:border-indigo-200 hover:bg-indigo-50' }}">
                            <span
                                class="block text-xs font-bold uppercase tracking-wide opacity-75">{{ $option['label'] }}</span>
                            <span data-status-count="{{ $value === '' ? 'semua' : $value }}"
                                class="mt-2 block text-2xl font-black">
                                {{ number_format($option['count'], 0, ',', '.') }}
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>

            <section class="mb-5 grid gap-5 lg:grid-cols-[1fr_.8fr]">
                <div class="clean-card rounded-3xl p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-500">Total Tagihan</p>
                        </div>
                    </div>
                    <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
                        @foreach ($tagihanOptions as $value => $label)
                            <button type="button" data-tagihan-range="{{ $value }}"
                                class="tagihanRangeBtn shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition {{ $selectedTagihan === $value ? 'border-rose-500 bg-rose-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                        <button type="button" data-sort="tagihan_desc"
                            class="sortBtn shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition {{ $selectedSort === 'tagihan_desc' ? 'border-slate-700 bg-slate-800 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800' }}">
                            Tagihan Terbesar
                        </button>
                    </div>
                </div>

                <div class="clean-card rounded-3xl p-4">
                    <div class="mb-3">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-500">Petugas</p>
                    </div>
                    <div id="petugasPerformanceContainer" class="space-y-3">
                        @include('admin.siswa.partials.petugas-performance', [
                            'petugasPerformance' => $petugasPerformance,
                        ])
                    </div>
                </div>
            </section>

            <div class="clean-card overflow-hidden rounded-3xl">
                <div id="tableLoading" class="hidden border-b border-indigo-100 bg-indigo-50/80 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-spinner fa-spin text-indigo-600"></i>
                        <div>
                            <p id="tableLoadingText" class="text-sm font-semibold text-indigo-900">Mengambil data siswa...
                            </p>
                            <p class="text-xs text-indigo-600">Sebentar ya, data pembayaran sedang dirapikan.</p>
                        </div>
                    </div>
                </div>
                <div id="tableContainer">
                    @include('admin.siswa.partials.table', ['siswa' => $siswa, 'petugas' => $petugas])
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const searchInput = document.getElementById('searchInput');
                const filterLembaga = document.getElementById('filterLembaga');
                const filterLembagaFormal = document.getElementById('filterLembagaFormal');
                const filterLembagaPondok = document.getElementById('filterLembagaPondok');
                const filterLembagaDiniyah = document.getElementById('filterLembagaDiniyah');
                const filterStatusPenanganan = document.getElementById('filterStatusPenanganan');
                const filterPeriodePenanganan = document.getElementById('filterPeriodePenanganan');
                const filterTagihan = document.getElementById('filterTagihan');
                const filterSort = document.getElementById('filterSort');
                const tableContainer = document.getElementById('tableContainer');
                const petugasPerformanceContainer = document.getElementById('petugasPerformanceContainer');
                const tableLoading = document.getElementById('tableLoading');
                const tableLoadingText = document.getElementById('tableLoadingText');
                const resetButton = document.getElementById('resetButton');
                const clearLembagaButton = document.getElementById('clearLembagaButton');
                const downloadSiswaLink = document.getElementById('downloadSiswaLink');
                const loadingMessages = [
                    'Menghitung tagihan siswa...',
                    'Menyempurnakan status pembayaran...',
                    'Mengambil data penanganan...',
                    'Merapikan daftar siswa...',
                    'Menghitung performa petugas...'
                ];
                let loadingInterval;

                function setActive(buttons, activeButton, activeClasses, inactiveClasses) {
                    buttons.forEach(item => {
                        item.classList.remove(...activeClasses);
                        item.classList.add(...inactiveClasses);
                    });
                    activeButton.classList.remove(...inactiveClasses);
                    activeButton.classList.add(...activeClasses);
                }

                function showTableLoading() {
                    let index = 0;
                    tableLoadingText.textContent = loadingMessages[index];
                    tableLoading.classList.remove('hidden');
                    tableContainer.classList.add('opacity-50', 'pointer-events-none');
                    clearInterval(loadingInterval);
                    loadingInterval = setInterval(() => {
                        index = (index + 1) % loadingMessages.length;
                        tableLoadingText.textContent = loadingMessages[index];
                    }, 1400);
                }

                function hideTableLoading() {
                    clearInterval(loadingInterval);
                    tableLoading.classList.add('hidden');
                    tableContainer.classList.remove('opacity-50', 'pointer-events-none');
                }

                function formatNumber(value) {
                    return new Intl.NumberFormat('id-ID').format(value || 0);
                }

                function updateStatusCards(stats) {
                    if (!stats) return;
                    document.querySelectorAll('[data-status-count]').forEach(element => {
                        const key = element.dataset.statusCount;
                        if (Object.prototype.hasOwnProperty.call(stats, key)) {
                            element.textContent = formatNumber(stats[key]);
                        }
                    });
                }

                function debounce(fn, ms) {
                    let timer;
                    return (...args) => {
                        clearTimeout(timer);
                        timer = setTimeout(() => fn.apply(this, args), ms);
                    };
                }

                function fetchSiswa(url = null) {
                    if (typeof url !== 'string') url = null;
                    showTableLoading();

                    if (!url) {
                        const params = new URLSearchParams({
                            search: searchInput.value,
                            lembaga_filter: filterLembaga.value,
                            tagihan_range: filterTagihan.value,
                            sort: filterSort.value,
                            status_penanganan: filterStatusPenanganan.value,
                            periode_penanganan: filterPeriodePenanganan.value,
                        });
                        url = `{{ route('admin.siswa.index') }}?${params.toString()}`;
                    }

                    updateDownloadLink();

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            tableContainer.innerHTML = data.html;
                            petugasPerformanceContainer.innerHTML = data.petugasPerformanceHtml;
                            updateStatusCards(data.statPenanganan);
                            window.history.replaceState({}, '', url);
                            hideTableLoading();
                        })
                        .catch(err => {
                            console.error('Error loading table:', err);
                            tableLoadingText.textContent = 'Gagal memuat data. Coba lagi sebentar.';
                            setTimeout(hideTableLoading, 1800);
                        });
                }

                function updateDownloadLink() {
                    const params = new URLSearchParams({
                        search: searchInput.value,
                        lembaga_filter: filterLembaga.value,
                        tagihan_range: filterTagihan.value,
                        sort: filterSort.value,
                        status_penanganan: filterStatusPenanganan.value,
                        periode_penanganan: filterPeriodePenanganan.value,
                    });

                    [...params.keys()].forEach(key => {
                        if (!params.get(key)) params.delete(key);
                    });

                    downloadSiswaLink.href = `{{ route('admin.siswa.export') }}?${params.toString()}`;
                }

                searchInput.addEventListener('keyup', debounce(fetchSiswa, 300));
                resetButton.addEventListener('click', () => window.location.href = window.location.pathname);
                clearLembagaButton.addEventListener('click', () => {
                    filterLembaga.value = '';
                    filterLembagaFormal.value = '';
                    filterLembagaPondok.value = '';
                    filterLembagaDiniyah.value = '';
                    fetchSiswa();
                });

                document.addEventListener('click', event => {
                    const pageLink = event.target.closest('.ajaxPage');
                    if (!pageLink) return;
                    event.preventDefault();
                    fetchSiswa(pageLink.href);
                    tableContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });

                [filterLembagaFormal, filterLembagaPondok, filterLembagaDiniyah].forEach(select => {
                    select.addEventListener('change', () => {
                        if (select.value) {
                            [filterLembagaFormal, filterLembagaPondok, filterLembagaDiniyah].forEach(
                                other => {
                                    if (other !== select) other.value = '';
                                });
                        }

                        filterLembaga.value = select.value;
                        fetchSiswa();
                    });
                });

                document.querySelectorAll('.statusPenangananBtn').forEach(button => {
                    button.addEventListener('click', () => {
                        filterStatusPenanganan.value = button.dataset.statusPenanganan || '';
                        setActive(
                            document.querySelectorAll('.statusPenangananBtn'),
                            button,
                            ['border-indigo-500', 'bg-indigo-600', 'text-white', 'shadow-sm'],
                            ['border-slate-200', 'bg-white', 'text-slate-700']
                        );
                        fetchSiswa();
                    });
                });

                document.querySelectorAll('.periodePenangananBtn').forEach(button => {
                    button.addEventListener('click', () => {
                        filterPeriodePenanganan.value = button.dataset.periodePenanganan || '';
                        setActive(
                            document.querySelectorAll('.periodePenangananBtn'),
                            button,
                            ['border-indigo-500', 'bg-indigo-600', 'text-white', 'shadow-sm'],
                            ['border-slate-200', 'bg-white', 'text-slate-600']
                        );
                        fetchSiswa();
                    });
                });

                document.querySelectorAll('.tagihanRangeBtn').forEach(button => {
                    button.addEventListener('click', () => {
                        filterTagihan.value = button.dataset.tagihanRange || '';
                        setActive(
                            document.querySelectorAll('.tagihanRangeBtn'),
                            button,
                            ['border-rose-500', 'bg-rose-600', 'text-white', 'shadow-sm'],
                            ['border-slate-200', 'bg-white', 'text-slate-600']
                        );
                        fetchSiswa();
                    });
                });

                document.querySelectorAll('.sortBtn').forEach(button => {
                    button.addEventListener('click', () => {
                        const nextSort = filterSort.value === button.dataset.sort ? '' : (button.dataset.sort || '');
                        filterSort.value = nextSort;
                        const active = nextSort === button.dataset.sort;
                        button.classList.toggle('border-slate-700', active);
                        button.classList.toggle('bg-slate-800', active);
                        button.classList.toggle('text-white', active);
                        button.classList.toggle('shadow-sm', active);
                        button.classList.toggle('border-slate-200', !active);
                        button.classList.toggle('bg-white', !active);
                        button.classList.toggle('text-slate-600', !active);
                        fetchSiswa();
                    });
                });
            });
        </script>
    @endpush
@endsection
