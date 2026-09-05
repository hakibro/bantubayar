@extends('layouts.dashboard')

@section('title', 'Manajemen Alumni')

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
            '' => ['label' => 'Semua Alumni', 'count' => $statPenanganan['semua'] ?? 0, 'tone' => 'slate'],
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
            '' => 'Semua Tunggakan',
            '0' => 'Rp 0',
            '1_500k' => 'Rp 1 - 500rb',
            '500k_1jt' => '500rb - 1jt',
            '1jt_2jt' => '1jt - 2jt',
            '2jt_plus' => '> 2jt',
        ];
        $tahunLulusOptions = \App\Models\Alumni::query()
            ->distinct()
            ->whereNotNull('tahun_lulus')
            ->orderByDesc('tahun_lulus')
            ->pluck('tahun_lulus');
        $selectedStatusPenanganan = request('status_penanganan', '');
        $selectedPeriodePenanganan = request('periode_penanganan', 'bulan_ini');
        $selectedTagihan = request('tagihan_range', '');
        $selectedLembaga = request('lembaga_filter', '');
        $selectedTahunLulus = request('tahun_lulus', '');
        $selectedSort = request('sort', '');
    @endphp

    <div class="clean-bg min-h-screen px-4 py-5 md:p-8">
        <div class="mx-auto max-w-7xl">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-500">Alumni</p>
                    <h1 class="mt-1 text-2xl font-black text-slate-800 md:text-3xl">Manajemen Alumni</h1>
                    <p class="mt-1 text-sm text-slate-500">Pantau alumni yang masih memiliki tunggakan pembayaran.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.alumni.export', request()->query()) }}"
                        class="inline-flex items-center rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">
                        <i class="fas fa-download mr-2"></i> Download Data Alumni
                    </a>
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
                <input id="filterTahunLulus" type="hidden" value="{{ $selectedTahunLulus }}">
                <input id="filterSort" type="hidden" value="{{ $selectedSort }}">

                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="block">
                        <span
                            class="mb-1 block text-[10px] font-black uppercase tracking-widest text-blue-500">Lembaga</span>
                        <select id="filterLembagaSelect"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-xs font-semibold text-slate-700 focus:border-blue-300 focus:ring-2 focus:ring-blue-200">
                            <option value="">Semua Lembaga</option>
                            @foreach ($daftarLembaga as $lembaga)
                                <option value="{{ $lembaga }}" {{ $selectedLembaga === $lembaga ? 'selected' : '' }}>
                                    {{ $lembaga }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-[10px] font-black uppercase tracking-widest text-purple-500">Tahun
                            Lulus</span>
                        <select id="filterTahunLulusSelect"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-xs font-semibold text-slate-700 focus:border-purple-300 focus:ring-2 focus:ring-purple-200">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahunLulusOptions as $tahun)
                                <option value="{{ $tahun }}" {{ $selectedTahunLulus == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span
                            class="mb-1 block text-[10px] font-black uppercase tracking-widest text-rose-500">Tunggakan</span>
                        <select id="filterTagihanSelect"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-3 py-3 text-xs font-semibold text-slate-700 focus:border-rose-300 focus:ring-2 focus:ring-rose-200">
                            @foreach ($tagihanOptions as $value => $label)
                                <option value="{{ $value }}" {{ $selectedTagihan === $value ? 'selected' : '' }}>
                                    {{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="clean-card mb-5 rounded-3xl p-4">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-bold tracking-normal text-gray-400">Periode & Status Penanganan</p>
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
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.18em] text-rose-500">Tunggakan Alumni</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-slate-700">Urutan tunggakan terbesar</span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold uppercase tracking-wide text-slate-500">OFF</span>
                                <button type="button" data-sort="tagihan_desc"
                                    class="sortToggleBtn relative inline-flex h-6 w-12 items-center rounded-full transition-colors {{ $selectedSort === 'tagihan_desc' ? 'bg-indigo-600' : 'bg-slate-300' }}">
                                    <span
                                        class="inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform {{ $selectedSort === 'tagihan_desc' ? 'translate-x-6' : 'translate-x-0.5' }}"></span>
                                </button>
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wide {{ $selectedSort === 'tagihan_desc' ? 'text-indigo-600' : 'text-slate-400' }}">ON</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 pb-1">
                        @foreach ($tagihanOptions as $value => $label)
                            <button type="button" data-tagihan-range="{{ $value }}"
                                class="tagihanRangeBtn shrink-0 rounded-full border px-4 py-2 text-xs font-bold transition {{ $selectedTagihan === $value ? 'border-rose-500 bg-rose-600 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-700' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="clean-card rounded-3xl p-4">
                    <div class="mb-3">
                        <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-500">Petugas</p>
                    </div>
                    <div id="petugasPerformanceContainer" class="space-y-3">
                        @include('admin.alumni.partials.petugas-performance', [
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
                            <p id="tableLoadingText" class="text-sm font-semibold text-indigo-900">Mengambil data
                                alumni...
                            </p>
                            <p class="text-xs text-indigo-600">Sebentar ya, data pembayaran sedang dirapikan.</p>
                        </div>
                    </div>
                </div>
                <div id="tableContainer">
                    @include('admin.alumni.partials.table', ['alumni' => $alumni])
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const searchInput = document.getElementById('searchInput');
                const filterLembaga = document.getElementById('filterLembaga');
                const filterLembagaSelect = document.getElementById('filterLembagaSelect');
                const filterStatusPenanganan = document.getElementById('filterStatusPenanganan');
                const filterPeriodePenanganan = document.getElementById('filterPeriodePenanganan');
                const filterTagihan = document.getElementById('filterTagihan');
                const filterTagihanSelect = document.getElementById('filterTagihanSelect');
                const filterTahunLulus = document.getElementById('filterTahunLulus');
                const filterTahunLulusSelect = document.getElementById('filterTahunLulusSelect');
                const filterSort = document.getElementById('filterSort');
                const tableContainer = document.getElementById('tableContainer');
                const petugasPerformanceContainer = document.getElementById('petugasPerformanceContainer');
                const tableLoading = document.getElementById('tableLoading');
                const tableLoadingText = document.getElementById('tableLoadingText');
                const resetButton = document.getElementById('resetButton');
                const loadingMessages = [
                    'Menghitung tunggakan alumni...',
                    'Menyempurnakan status pembayaran...',
                    'Mengambil data penanganan...',
                    'Merapikan daftar alumni...',
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

                function fetchAlumni(url = null) {
                    if (typeof url !== 'string') url = null;
                    showTableLoading();

                    if (!url) {
                        const params = new URLSearchParams({
                            search: searchInput.value,
                            lembaga_filter: filterLembaga.value,
                            tagihan_range: filterTagihan.value,
                            tahun_lulus: filterTahunLulus.value,
                            sort: filterSort.value,
                            status_penanganan: filterStatusPenanganan.value,
                            periode_penanganan: filterPeriodePenanganan.value,
                        });
                        url = `{{ route('admin.alumni.index') }}?${params.toString()}`;
                    }

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

                searchInput.addEventListener('keyup', debounce(fetchAlumni, 300));
                resetButton.addEventListener('click', () => window.location.href = window.location.pathname);

                filterLembagaSelect.addEventListener('change', () => {
                    filterLembaga.value = filterLembagaSelect.value;
                    fetchAlumni();
                });

                filterTahunLulusSelect.addEventListener('change', () => {
                    filterTahunLulus.value = filterTahunLulusSelect.value;
                    fetchAlumni();
                });

                filterTagihanSelect.addEventListener('change', () => {
                    filterTagihan.value = filterTagihanSelect.value;
                    setActive(
                        document.querySelectorAll('.tagihanRangeBtn'),
                        document.querySelector(
                            `.tagihanRangeBtn[data-tagihan-range="${filterTagihan.value}"]`),
                        ['border-rose-500', 'bg-rose-600', 'text-white', 'shadow-sm'],
                        ['border-slate-200', 'bg-white', 'text-slate-600']
                    );
                    fetchAlumni();
                });

                document.addEventListener('click', event => {
                    const pageLink = event.target.closest('.ajaxPage');
                    if (!pageLink) return;
                    event.preventDefault();
                    fetchAlumni(pageLink.href);
                    tableContainer.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
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
                        fetchAlumni();
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
                        fetchAlumni();
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
                        if (filterTagihanSelect) filterTagihanSelect.value = filterTagihan.value;
                        fetchAlumni();
                    });
                });

                document.querySelectorAll('.sortToggleBtn').forEach(button => {
                    button.addEventListener('click', () => {
                        const isActive = filterSort.value === button.dataset.sort;
                        const nextSort = isActive ? '' : (button.dataset.sort || '');
                        filterSort.value = nextSort;
                        const willBeActive = !isActive;

                        button.classList.toggle('bg-indigo-600', willBeActive);
                        button.classList.toggle('bg-slate-300', !willBeActive);

                        const switchCircle = button.querySelector('span');
                        switchCircle.classList.toggle('translate-x-6', willBeActive);
                        switchCircle.classList.toggle('translate-x-0.5', !willBeActive);

                        const onText = button.parentElement.querySelector('span:last-child');
                        onText.classList.toggle('text-indigo-600', willBeActive);
                        onText.classList.toggle('text-slate-400', !willBeActive);

                        fetchAlumni();
                    });
                });
            });
        </script>
    @endpush
@endsection
