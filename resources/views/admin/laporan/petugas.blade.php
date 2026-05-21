@extends('layouts.dashboard')

@section('content')
    @php
        $formatLabel = fn($value) => str($value ?: 'tanpa_hasil')->replace('_', ' ')->title();
        $activeCount = $menungguRespon + $menungguTindakLanjut;

        $statusMeta = [
            'selesai' => ['label' => 'Selesai', 'icon' => 'fa-circle-check', 'class' => 'bg-emerald-100 text-emerald-700', 'bar' => 'bg-emerald-500'],
            'menunggu_respon' => ['label' => 'Menunggu Respon', 'icon' => 'fa-clock', 'class' => 'bg-amber-100 text-amber-700', 'bar' => 'bg-amber-500'],
            'menunggu_tindak_lanjut' => ['label' => 'Tindak Lanjut', 'icon' => 'fa-rotate-right', 'class' => 'bg-blue-100 text-blue-700', 'bar' => 'bg-blue-500'],
        ];

        $summaryCards = [
            ['label' => 'Penanganan Tunggakan', 'value' => number_format($totalTunggakan), 'caption' => $tunggakanSuccessRate . '% berhasil selesai', 'icon' => 'fa-file-invoice-dollar', 'theme' => 'from-rose-50 to-white border-rose-100 text-rose-600'],
            ['label' => 'Apresiasi Lunas', 'value' => number_format($totalApresiasi), 'caption' => $apresiasiCompletionRate . '% apresiasi selesai', 'icon' => 'fa-hands-clapping', 'theme' => 'from-emerald-50 to-white border-emerald-100 text-emerald-600'],
            ['label' => 'Total Aktivitas', 'value' => number_format($totalPenanganan), 'caption' => $completionRate . '% semua aktivitas selesai', 'icon' => 'fa-clipboard-list', 'theme' => 'from-blue-50 to-white border-blue-100 text-blue-600'],
            ['label' => 'Rating', 'value' => number_format($ratingAvg, 1) . '/5', 'caption' => number_format($totalDinilai) . ' penilaian masuk', 'icon' => 'fa-star', 'theme' => 'from-violet-50 to-white border-violet-100 text-violet-600'],
        ];

        $rangeOptions = [
            'current_week' => 'Minggu Ini',
            'last_week' => 'Minggu Lalu',
            'current_month' => 'Bulan Ini',
            'previous_month' => 'Sebelumnya',
            'all' => 'Semua',
        ];

        $detailFilters = [
            'all' => 'Semua',
            'tunggakan' => 'Tunggakan',
            'apresiasi' => 'Apresiasi',
            'lunas' => 'Hasil Lunas',
            'cicilan' => 'Cicilan',
            'isi_saldo' => 'Isi Saldo',
            'hp_tidak_aktif' => 'WA Nonaktif',
            'tidak_ada_respon' => 'Wali Tidak Merespon',
            'aktif' => 'Aktif',
        ];
    @endphp

    <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-primary">Laporan Petugas</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-950 sm:text-3xl">Performa Penanganan Siswa</h1>
                <p class="mt-2 text-sm text-gray-500">
                    {{ $periodLabel }}
                    @if ($selectedPetugas)
                        oleh {{ $selectedPetugas->name }}
                    @else
                        untuk semua petugas
                    @endif
                </p>
            </div>
        </div>

        <section class="mb-6 rounded-[1.5rem] border border-gray-100 bg-white p-4 shadow-sm sm:p-5">
            <form id="reportFilterForm" method="GET" action="{{ route('admin.laporan.petugas') }}" class="space-y-4">
                <input type="hidden" name="range" value="{{ $range }}">
                @if ($range === 'custom')
                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                @endif

                <div class="flex items-end gap-2 sm:gap-3">
                    <div class="min-w-0 flex-1">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-400">Petugas</label>
                        <select name="petugas_id" onchange="document.getElementById('reportFilterForm').submit()"
                            class="h-12 w-full rounded-full border border-gray-200 bg-gray-50 px-4 text-sm font-semibold text-gray-800 outline-none transition focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10">
                            <option value="">Semua Petugas</option>
                            @foreach ($petugasList as $p)
                                <option value="{{ $p->id }}" @selected($petugasId == $p->id)>
                                    {{ $p->name }}{{ $p->lembaga ? ' - ' . $p->lembaga : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="button" onclick="openDateRangeModal()" title="Pilih rentang tanggal"
                        class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-full border px-3 text-sm font-bold transition sm:px-5
                        {{ $range === 'custom' ? 'border-primary bg-primary text-white shadow-md shadow-primary/20' : 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-white' }}">
                        <i class="fas fa-calendar-days"></i>
                        <span class="hidden sm:inline">Rentang</span>
                    </button>

                    <a href="{{ route('admin.laporan.petugas') }}" title="Reset filter"
                        class="flex h-12 shrink-0 items-center justify-center gap-2 rounded-full border border-gray-200 bg-gray-50 px-3 text-sm font-bold text-gray-700 transition hover:bg-white sm:px-5">
                        <i class="fas fa-rotate-left"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </a>
                </div>

                <div class="no-scrollbar flex gap-2 overflow-x-auto pb-1">
                    @foreach ($rangeOptions as $key => $label)
                        <a href="{{ route('admin.laporan.petugas', array_filter(['range' => $key, 'petugas_id' => $petugasId])) }}"
                            class="shrink-0 rounded-full px-4 py-2 text-sm font-bold transition
                            {{ $range === $key ? 'bg-primary text-white shadow-md shadow-primary/20' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </form>
        </section>

        <div class="mb-6 grid grid-cols-2 gap-3 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <div class="rounded-[1.15rem] border bg-gradient-to-br p-3 shadow-sm sm:rounded-[1.5rem] sm:p-5 {{ $card['theme'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold text-gray-500 sm:text-sm">{{ $card['label'] }}</p>
                            <p class="mt-2 text-2xl font-black leading-none text-gray-950 sm:mt-3 sm:text-3xl">{{ $card['value'] }}</p>
                        </div>
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/80 shadow-sm sm:h-12 sm:w-12 sm:rounded-2xl">
                            <i class="fas {{ $card['icon'] }} text-sm sm:text-lg"></i>
                        </span>
                    </div>
                    <p class="mt-3 line-clamp-2 text-xs font-medium text-gray-500 sm:mt-5 sm:text-sm">{{ $card['caption'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="rounded-[1.5rem] border border-gray-100 bg-white p-5 shadow-sm xl:col-span-2">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-950">Status Penanganan</h2>
                        <p class="text-sm text-gray-500">Komposisi pekerjaan selama periode laporan.</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">
                        {{ number_format($totalPenanganan) }} total
                    </span>
                </div>

                <div class="space-y-5">
                    @foreach ($statusMeta as $status => $meta)
                        @php
                            $count = $statusBreakdown[$status] ?? 0;
                            $percent = round(($count / max($totalPenanganan, 1)) * 100, 1);
                        @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-2xl {{ $meta['class'] }}">
                                        <i class="fas {{ $meta['icon'] }}"></i>
                                    </span>
                                    <p class="text-sm font-bold text-gray-800">{{ $meta['label'] }}</p>
                                </div>
                                <p class="text-sm font-black text-gray-950">{{ number_format($count) }} <span class="font-semibold text-gray-400">({{ $percent }}%)</span></p>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full {{ $meta['bar'] }}" style="width: {{ min($percent, 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-[1.5rem] border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-950">Kecepatan Selesai</h2>
                        <p class="mt-1 text-sm text-gray-500">Rata-rata durasi selesai.</p>
                    </div>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-700">
                        <i class="fas fa-stopwatch"></i>
                    </span>
                </div>
                <div class="mt-5 flex items-end gap-2">
                    <p class="text-5xl font-black leading-none text-gray-950">{{ number_format($rataJamSelesai, 1) }}</p>
                    <p class="pb-1 text-sm font-bold text-gray-500">jam</p>
                </div>
                <div class="mt-5 rounded-[1rem] bg-gray-50 px-4 py-3">
                    <p class="text-sm font-semibold text-gray-600">
                        Rating {{ number_format($ratingAvg, 1) }}/5 dari {{ number_format($totalDinilai) }} penilaian.
                    </p>
                </div>
            </section>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="rounded-[1.5rem] border border-gray-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-gray-950">Jenis Penanganan</h2>
                <p class="mb-4 text-sm text-gray-500">Tunggakan dipisah dari apresiasi siswa lunas.</p>
                <div class="mb-4 grid grid-cols-2 gap-3">
                    <div class="rounded-[1rem] bg-rose-50 p-3">
                        <p class="text-xs font-black uppercase text-rose-500">Tunggakan</p>
                        <p class="mt-1 text-2xl font-black text-gray-950">{{ number_format($totalTunggakan) }}</p>
                        <p class="mt-1 text-xs font-semibold text-rose-600">{{ number_format($selesaiTunggakan) }} selesai, {{ number_format($aktifTunggakan) }} aktif</p>
                    </div>
                    <div class="rounded-[1rem] bg-emerald-50 p-3">
                        <p class="text-xs font-black uppercase text-emerald-500">Apresiasi</p>
                        <p class="mt-1 text-2xl font-black text-gray-950">{{ number_format($totalApresiasi) }}</p>
                        <p class="mt-1 text-xs font-semibold text-emerald-600">{{ number_format($selesaiApresiasi) }} selesai, {{ number_format($aktifApresiasi) }} aktif</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse ($hasilBreakdown as $type => $rows)
                        @php
                            $typeTotal = $rows->sum('total');
                            $typeLabel = $type === 'tunggakan' ? 'Hasil Tunggakan' : 'Hasil Apresiasi';
                            $typeColor = $type === 'tunggakan' ? 'bg-rose-500' : 'bg-emerald-500';
                        @endphp
                        <div>
                            <p class="mb-2 text-xs font-black uppercase tracking-wide text-gray-400">{{ $typeLabel }}</p>
                            <div class="space-y-2">
                                @foreach ($rows as $row)
                                    @php $percent = round(($row->total / max($typeTotal, 1)) * 100, 1); @endphp
                                    <div class="rounded-[1rem] border border-gray-100 p-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-sm font-bold text-gray-700">{{ $formatLabel($row->hasil) }}</p>
                                            <p class="text-sm font-black text-gray-950">{{ number_format($row->total) }}</p>
                                        </div>
                                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full rounded-full {{ $typeColor }}" style="width: {{ min($percent, 100) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="rounded-[1rem] bg-gray-50 p-5 text-center text-sm text-gray-500">
                            Belum ada penanganan selesai pada periode ini.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-[1.5rem] border border-gray-100 bg-white p-5 shadow-sm xl:col-span-2">
                <h2 class="text-lg font-bold text-gray-950">Performa Bendahara/Petugas</h2>
                <p class="mb-4 text-sm text-gray-500">Semua bendahara/petugas tampil, termasuk yang belum punya penanganan pada periode ini.</p>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-bold uppercase text-gray-400">
                                <th class="px-3 py-3">Petugas</th>
                                <th class="px-3 py-3 text-right">Siswa Terkait</th>
                                <th class="px-3 py-3 text-right">Tunggakan</th>
                                <th class="px-3 py-3 text-right">Berhasil</th>
                                <th class="px-3 py-3 text-right">% Berhasil</th>
                                <th class="px-3 py-3 text-right">Apresiasi</th>
                                <th class="px-3 py-3 text-right">Coverage</th>
                                <th class="px-3 py-3 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($petugasPerformance as $item)
                                <tr>
                                    <td class="px-3 py-3">
                                        <p class="font-bold text-gray-950">{{ $item->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->roles_label ?: 'Petugas' }}{{ $item->lembaga ? ' - ' . $item->lembaga : '' }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <p class="font-bold text-gray-950">{{ number_format($item->related_siswa) }}</p>
                                        <p class="text-xs font-semibold text-rose-500">{{ number_format($item->related_tunggakan_siswa) }} tunggakan</p>
                                    </td>
                                    <td class="px-3 py-3 text-right font-bold text-rose-700">{{ number_format($item->total_tunggakan) }}</td>
                                    <td class="px-3 py-3 text-right font-semibold text-emerald-700">{{ number_format($item->selesai_tunggakan) }}</td>
                                    <td class="px-3 py-3 text-right font-bold text-gray-950">{{ $item->tunggakan_success_rate }}%</td>
                                    <td class="px-3 py-3 text-right font-semibold text-emerald-700">{{ number_format($item->total_apresiasi) }}</td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="ml-auto w-24">
                                            <p class="mb-1 text-xs font-bold text-gray-700">{{ $item->tunggakan_coverage_rate }}%</p>
                                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full rounded-full bg-rose-500" style="width: {{ min($item->tunggakan_coverage_rate, 100) }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <button type="button" onclick="openPetugasModal('petugasModal{{ $item->id }}')"
                                            class="inline-flex h-9 items-center justify-center rounded-full bg-gray-100 px-3 text-xs font-bold text-gray-700 transition hover:bg-primary hover:text-white">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-gray-500">Belum ada data petugas pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

    </div>

    @foreach ($petugasPerformance as $item)
        @php $detailPenanganan = $penangananPetugas->get($item->id, collect()); @endphp
        <div id="petugasModal{{ $item->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
            <div class="max-h-[88vh] w-full max-w-3xl overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
                <div class="flex items-start justify-between gap-4 border-b border-gray-100 p-5">
                    <div class="flex flex-1 flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-primary">Detail Petugas</p>
                            <h2 class="mt-1 text-xl font-black text-gray-950">{{ $item->name }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ $item->roles_label ?: 'Petugas' }}{{ $item->lembaga ? ' - ' . $item->lembaga : '' }} - {{ number_format($item->total) }} penanganan</p>
                        </div>
                        <div class="grid w-full grid-cols-4 gap-2 md:w-auto md:min-w-[24rem]">
                            <div class="rounded-[1rem] bg-gray-50 p-3">
                                <p class="text-[10px] font-bold uppercase text-gray-400">Siswa</p>
                                <p class="mt-1 text-xl font-black text-gray-950">{{ number_format($item->related_siswa) }}</p>
                            </div>
                            <div class="rounded-[1rem] bg-rose-50 p-3">
                                <p class="text-[10px] font-bold uppercase text-rose-500">Tunggakan</p>
                                <p class="mt-1 text-xl font-black text-gray-950">{{ number_format($item->total_tunggakan) }}</p>
                            </div>
                            <div class="rounded-[1rem] bg-emerald-50 p-3">
                                <p class="text-[10px] font-bold uppercase text-emerald-500">Berhasil</p>
                                <p class="mt-1 text-xl font-black text-gray-950">{{ $item->tunggakan_success_rate }}%</p>
                            </div>
                            <div class="rounded-[1rem] bg-primary/10 p-3">
                                <p class="text-[10px] font-bold uppercase text-primary">Apresiasi</p>
                                <p class="mt-1 text-xl font-black text-gray-950">{{ number_format($item->total_apresiasi) }}</p>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="closePetugasModal('petugasModal{{ $item->id }}')"
                        class="flex h-10 w-10 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="border-b border-gray-100 px-5 py-3">
                    <div class="no-scrollbar flex gap-2 overflow-x-auto">
                        @foreach ($detailFilters as $filterKey => $filterLabel)
                            <button type="button"
                                onclick="filterPetugasDetail('petugasModal{{ $item->id }}', '{{ $filterKey }}')"
                                data-filter-chip="{{ $filterKey }}"
                                class="shrink-0 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-600 transition hover:bg-primary hover:text-white">
                                {{ $filterLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="max-h-[56vh] overflow-y-auto px-5 py-5">
                    <div class="space-y-3">
                        @forelse ($detailPenanganan as $detail)
                            @php
                                $meta = $statusMeta[$detail->status] ?? ['label' => $formatLabel($detail->status), 'class' => 'bg-gray-100 text-gray-700'];
                                $filterValue = $detail->status === 'selesai' ? ($detail->hasil ?? 'tanpa_hasil') : 'aktif';
                                $detailType = ((int) ($detail->is_penanganan_tunggakan ?? 0)) === 1 ? 'tunggakan' : 'apresiasi';
                            @endphp
                            <article class="petugas-detail-item rounded-[1.15rem] border border-gray-100"
                                data-filter="{{ $filterValue }}"
                                data-type="{{ $detailType }}"
                                data-hasil="{{ $detail->hasil ?? '' }}"
                                data-status="{{ $detail->status }}">
                                <button type="button" onclick="togglePetugasAccordion('detail{{ $detail->id }}')"
                                    class="flex w-full items-start justify-between gap-3 p-4 text-left">
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-gray-950">{{ $detail->siswa->nama ?? 'Siswa tidak ditemukan' }}</p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ $detail->created_at?->translatedFormat('d M Y H:i') }} - {{ $detailType === 'tunggakan' ? 'Penanganan Tunggakan' : 'Apresiasi Lunas' }}
                                        </p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-2">
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $detailType === 'tunggakan' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            {{ $detailType === 'tunggakan' ? 'Tunggakan' : 'Apresiasi' }}
                                        </span>
                                        <span class="rounded-full px-3 py-1 text-xs font-bold {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                                        <i id="icondetail{{ $detail->id }}" class="fas fa-chevron-down text-xs text-gray-400 transition-transform"></i>
                                    </div>
                                </button>

                                <div id="detail{{ $detail->id }}" class="hidden border-t border-gray-100 px-4 pb-4 pt-3">
                                    <div class="grid grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <p class="text-xs font-bold uppercase text-gray-400">Tipe</p>
                                            <p class="mt-1 font-bold text-gray-800">{{ $detailType === 'tunggakan' ? 'Penanganan Tunggakan' : 'Apresiasi Lunas' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase text-gray-400">Hasil</p>
                                            <p class="mt-1 font-bold text-gray-800">{{ $formatLabel($detail->hasil ?? '-') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold uppercase text-gray-400">Aksi</p>
                                            <p class="mt-1 font-bold text-gray-800">{{ number_format($detail->histories_count) }} riwayat</p>
                                        </div>
                                        <div class="col-span-2">
                                            <p class="text-xs font-bold uppercase text-gray-400">Kesanggupan</p>
                                            <p class="mt-1 font-bold text-gray-800">
                                                {{ $detail->kesanggupanTerakhir?->tanggal ? \Carbon\Carbon::parse($detail->kesanggupanTerakhir->tanggal)->translatedFormat('d M Y') : '-' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <p class="text-xs font-bold uppercase text-gray-400">History Aksi</p>
                                        <div class="mt-2 space-y-2">
                                            @forelse ($detail->histories as $history)
                                                <div class="rounded-xl bg-gray-50 p-3 text-sm">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <p class="font-bold text-gray-800">{{ $formatLabel($history->jenis_penanganan) }}</p>
                                                        <p class="text-xs text-gray-400">{{ $history->created_at?->translatedFormat('d M Y H:i') }}</p>
                                                    </div>
                                                    @if ($history->catatan)
                                                        <p class="mt-1 text-gray-600">{{ $history->catatan }}</p>
                                                    @endif
                                                </div>
                                            @empty
                                                <p class="rounded-xl bg-gray-50 p-3 text-sm text-gray-500">Belum ada history aksi.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-[1.15rem] bg-gray-50 px-5 py-10 text-center text-sm text-gray-500">
                                Belum ada detail penanganan untuk petugas ini.
                            </div>
                        @endforelse
                        <div class="petugas-empty-filter hidden rounded-[1.15rem] bg-gray-50 px-5 py-10 text-center text-sm text-gray-500">
                            Tidak ada penanganan untuk filter ini.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div id="dateRangeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
        <div class="w-full max-w-sm rounded-[1.75rem] bg-white p-5 shadow-2xl">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-black text-gray-950">Pilih Tanggal</h2>
                    <p class="mt-1 text-sm text-gray-500">Tap tanggal awal lalu tanggal akhir.</p>
                </div>
                <button type="button" onclick="closeDateRangeModal()"
                    class="flex h-10 w-10 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form method="GET" action="{{ route('admin.laporan.petugas') }}">
                <input type="hidden" name="range" value="custom">
                <input type="hidden" id="calendarStartInput" name="start_date" value="{{ $startDate ?? now()->startOfMonth()->format('Y-m-d') }}">
                <input type="hidden" id="calendarEndInput" name="end_date" value="{{ $endDate ?? now()->format('Y-m-d') }}">
                @if ($petugasId)
                    <input type="hidden" name="petugas_id" value="{{ $petugasId }}">
                @endif

                <div class="mb-4 grid grid-cols-2 gap-3">
                    <div class="rounded-[1rem] bg-gray-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Mulai</p>
                        <p id="calendarStartLabel" class="mt-1 text-sm font-black text-gray-950">-</p>
                    </div>
                    <div class="rounded-[1rem] bg-gray-50 p-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Akhir</p>
                        <p id="calendarEndLabel" class="mt-1 text-sm font-black text-gray-950">-</p>
                    </div>
                </div>

                <div class="rounded-[1.25rem] border border-gray-100 p-3">
                    <div class="mb-3 flex items-center justify-between">
                        <button type="button" onclick="changeCalendarMonth(-1)" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <p id="calendarMonthLabel" class="text-sm font-black text-gray-950"></p>
                        <button type="button" onclick="changeCalendarMonth(1)" class="flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-700">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </div>

                    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-bold uppercase text-gray-400">
                        <span>Min</span>
                        <span>Sen</span>
                        <span>Sel</span>
                        <span>Rab</span>
                        <span>Kam</span>
                        <span>Jum</span>
                        <span>Sab</span>
                    </div>
                    <div id="calendarGrid" class="mt-2 grid grid-cols-7 gap-1"></div>
                </div>

                <button type="submit"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-5 py-3 text-sm font-black text-white shadow-md shadow-primary/20 transition hover:bg-primary/90">
                    <i class="fas fa-check"></i>
                    Terapkan Rentang
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let calendarStart = parseCalendarDate(@json($startDate ?? now()->startOfMonth()->format('Y-m-d')));
        let calendarEnd = parseCalendarDate(@json($endDate ?? now()->format('Y-m-d')));
        let calendarView = new Date(calendarStart.getFullYear(), calendarStart.getMonth(), 1);
        let awaitingCalendarEnd = false;

        function parseCalendarDate(value) {
            const parts = value.split('-').map(Number);
            return new Date(parts[0], parts[1] - 1, parts[2]);
        }

        function formatCalendarValue(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatCalendarLabel(date) {
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function sameCalendarDay(a, b) {
            return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
        }

        function inCalendarRange(date) {
            return calendarStart && calendarEnd && date > calendarStart && date < calendarEnd;
        }

        function syncCalendarLabels() {
            document.getElementById('calendarStartInput').value = formatCalendarValue(calendarStart);
            document.getElementById('calendarEndInput').value = formatCalendarValue(calendarEnd ?? calendarStart);
            document.getElementById('calendarStartLabel').innerText = formatCalendarLabel(calendarStart);
            document.getElementById('calendarEndLabel').innerText = calendarEnd ? formatCalendarLabel(calendarEnd) : 'Pilih akhir';
        }

        function renderCalendar() {
            const grid = document.getElementById('calendarGrid');
            const monthLabel = document.getElementById('calendarMonthLabel');
            grid.innerHTML = '';
            monthLabel.innerText = calendarView.toLocaleDateString('id-ID', {
                month: 'long',
                year: 'numeric'
            });

            const year = calendarView.getFullYear();
            const month = calendarView.getMonth();
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDay; i++) {
                grid.appendChild(document.createElement('span'));
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const button = document.createElement('button');
                button.type = 'button';
                button.innerText = day;
                button.className = 'h-10 rounded-full text-sm font-bold transition';

                if (sameCalendarDay(date, calendarStart) || sameCalendarDay(date, calendarEnd)) {
                    button.className += ' bg-primary text-white shadow-md shadow-primary/20';
                } else if (inCalendarRange(date)) {
                    button.className += ' bg-primary/10 text-primary';
                } else {
                    button.className += ' text-gray-700 hover:bg-gray-100';
                }

                button.addEventListener('click', function() {
                    if (!awaitingCalendarEnd) {
                        calendarStart = date;
                        calendarEnd = null;
                        awaitingCalendarEnd = true;
                    } else if (date < calendarStart) {
                        calendarEnd = calendarStart;
                        calendarStart = date;
                        awaitingCalendarEnd = false;
                    } else {
                        calendarEnd = date;
                        awaitingCalendarEnd = false;
                    }

                    syncCalendarLabels();
                    renderCalendar();
                });

                grid.appendChild(button);
            }
        }

        function changeCalendarMonth(offset) {
            calendarView = new Date(calendarView.getFullYear(), calendarView.getMonth() + offset, 1);
            renderCalendar();
        }

        function openDateRangeModal() {
            const modal = document.getElementById('dateRangeModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            syncCalendarLabels();
            renderCalendar();
        }

        function closeDateRangeModal() {
            const modal = document.getElementById('dateRangeModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openPetugasModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closePetugasModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function closeAllPetugasModals() {
            document.querySelectorAll('[id^="petugasModal"]').forEach(function(modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        }

        function togglePetugasAccordion(id) {
            const content = document.getElementById(id);
            const icon = document.getElementById(`icon${id}`);
            content.classList.toggle('hidden');
            icon?.classList.toggle('rotate-180');
        }

        function filterPetugasDetail(modalId, filter) {
            const modal = document.getElementById(modalId);
            const items = modal.querySelectorAll('.petugas-detail-item');
            const empty = modal.querySelector('.petugas-empty-filter');
            let visibleCount = 0;

            modal.querySelectorAll('[data-filter-chip]').forEach(function(chip) {
                const active = chip.dataset.filterChip === filter;
                chip.classList.toggle('bg-primary', active);
                chip.classList.toggle('text-white', active);
                chip.classList.toggle('bg-gray-100', !active);
                chip.classList.toggle('text-gray-600', !active);
            });

            items.forEach(function(item) {
                const visible = filter === 'all' || item.dataset.type === filter || item.dataset.filter === filter || item.dataset.hasil === filter;
                item.classList.toggle('hidden', !visible);
                if (visible) {
                    visibleCount++;
                }
            });

            empty?.classList.toggle('hidden', visibleCount > 0);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDateRangeModal();
                closeAllPetugasModals();
            }
        });

        document.getElementById('dateRangeModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeDateRangeModal();
            }
        });

        document.querySelectorAll('[id^="petugasModal"]').forEach(function(modal) {
            modal.addEventListener('click', function(event) {
                if (event.target === this) {
                    closePetugasModal(this.id);
                }
            });
            filterPetugasDetail(modal.id, 'all');
        });
    </script>
@endpush
