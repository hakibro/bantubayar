@extends('layouts.dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8 bg-slate-50 min-h-screen">

        {{-- HEADER --}}
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Halo, {{ Auth::user()->name }} 👋</h1>
                <p class="text-sm text-slate-500">Berikut adalah ringkasan penanganan Anda hari ini.</p>
            </div>
            <a href="{{ route('penanganan.index') }}"
                class="bg-white border border-slate-200 px-4 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm">
                Lihat Semua Data
            </a>
        </div>

        {{-- QUICK FILTER TABS --}}
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <div class="flex bg-slate-200/60 p-1 rounded-2xl w-fit" id="filter-container">
                @foreach (['current_week' => 'Minggu Ini', 'last_week' => 'Minggu Lalu', 'older' => 'Sebelumnya'] as $key => $label)
                    <button data-range="{{ $key }}"
                        class="filter-btn px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $range == $key ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>



        {{-- CONTAINER UNTUK AJAX --}}
        <div id="dashboard-content" class="transition-opacity duration-300">
            @include('petugas.dashboard.partials.cards')
        </div>


        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- AREA UTAMA: TUGAS AKTIF & TERLAMBAT --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-list-ul text-blue-500"></i>
                        Daftar Kerja Prioritas
                    </h2>
                    <span class="text-[10px] bg-slate-200 px-2 py-1 rounded-full font-bold text-slate-600">
                        {{ $tugasAktif->count() }} TUGAS
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($tugasAktif as $item)
                        @php
                            $isTerlambat = $penangananTerlambat->contains('id', $item->id);
                        @endphp
                        <div
                            class="group relative bg-white border {{ $isTerlambat ? 'border-red-200' : 'border-slate-100' }} rounded-2xl p-4 hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-300">
                            <div class="flex items-center gap-4">
                                {{-- Status Indicator --}}
                                <div
                                    class="hidden md:flex flex-shrink-0 w-12 h-12 {{ $isTerlambat ? 'bg-red-50 text-red-500' : 'bg-slate-50 text-slate-400' }} rounded-xl items-center justify-center text-xl font-bold transition-colors">
                                    <i class="fas {{ $isTerlambat ? 'fa-exclamation-circle' : 'fa-user-clock' }}"></i>
                                </div>

                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-slate-800 truncate">{{ $item->siswa->nama }}</h3>
                                        @if ($isTerlambat)
                                            <span
                                                class="bg-red-100 text-red-600 text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-tighter">Terlambat</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap items-center text-xs text-slate-500 gap-y-1">
                                        <span
                                            class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-medium mr-2 capitalize">
                                            {{ str_replace('_', ' ', $item->lastHistory?->jenis_penanganan ?? 'Tidak Diketahui') }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <i class="far fa-clock"></i> {{ $item->lama_menunggu }}
                                        </span>
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center gap-3">
                                    <a href="{{ route('penanganan.show', $item->siswa->id) }}"
                                        class="flex items-center gap-2 bg-slate-900 text-white px-4 py-2 rounded-xl text-xs font-bold hover:bg-blue-600 transition-all group-hover:translate-x-1">
                                        Tangani <i class="fas fa-chevron-right text-[10px]"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white border border-dashed border-slate-300 rounded-3xl p-12 text-center">
                            <img src="https://illustrations.popsy.co/slate/shiba-inu.svg"
                                class="w-32 mx-auto mb-4 opacity-50">
                            <p class="text-slate-500 font-medium">Semua tugas sudah beres! Istirahat sejenak.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- SIDEBAR: INSIGHTS --}}
            <div class="space-y-6">
                {{-- Statistik Ringkas --}}
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
                    <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-pie text-emerald-500"></i> Performa Respon
                    </h3>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="text-4xl font-black text-slate-800">{{ $statistikRespon['rata_rata'] }}</div>
                        <div class="text-xs text-slate-500 leading-tight">
                            Rata-rata rating dari<br><span
                                class="font-bold text-slate-800">{{ $statistikRespon['total_dinilai'] }} wali</span>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-xs">
                            <span class="text-slate-500">Responsif (4-5)</span>
                            <span class="font-bold text-emerald-600">{{ $statistikRespon['responsif'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full"
                                style="width: {{ ($statistikRespon['responsif'] / max($statistikRespon['total_dinilai'], 1)) * 100 }}%">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Catatan Terbaru --}}
                <div class="bg-slate-900 rounded-2xl p-6 text-white shadow-lg">
                    <h3 class="font-bold mb-4 text-slate-300 text-xs uppercase tracking-widest">Feedback Terbaru</h3>
                    <div class="space-y-4">
                        @foreach ($catatanTerbaru as $catatan)
                            <div class="border-l-2 border-slate-700 pl-4 py-1">
                                <p class="text-[11px] font-bold text-blue-400 mb-1 capitalize">Wali
                                    {{ $catatan->siswa->nama }}</p>
                                <p class="text-xs text-slate-300 italic italic leading-relaxed">
                                    "{{ Str::limit($catatan->catatan, 60) }}"</p>
                                <div class="flex mt-2 text-[10px] text-yellow-500">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $catatan->rating ? 'fas' : 'far' }} fa-star"></i>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const range = this.getAttribute('data-range');
                    const container = document.getElementById('dashboard-content');

                    // UI Feedback: Loading
                    container.style.opacity = '0.5';

                    // Update active state button
                    document.querySelectorAll('.filter-btn').forEach(b => {
                        b.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
                        b.classList.add('text-slate-500');
                    });
                    this.classList.add('bg-white', 'text-blue-600', 'shadow-sm');

                    // AJAX Fetch
                    fetch(`{{ route('petugas.dashboard') }}?range=${range}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.text())
                        .then(html => {
                            container.innerHTML = html;
                            container.style.opacity = '1';
                        })
                        .catch(err => {
                            console.error(err);
                            container.style.opacity = '1';
                        });
                });
            });
        </script>
    @endpush
@endsection
