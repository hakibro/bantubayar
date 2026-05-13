@php
    $periodeLabel = match ($range) {
        'current_week' => 'Senin - Minggu Ini',
        'last_week' => 'Senin - Minggu Lalu',
        'current_month' => 'Bulan Ini (' . now()->translatedFormat('F') . ')',
        'older' => 'Data Lama (Sebelum Minggu Lalu)',
        'all' => 'Semua Waktu',
        default => 'Rentang Waktu',
    };

    $totalSiswa = max((int) ($statistikSiswa['total_siswa'] ?? 0), 1);
    $siswaDitangani = (int) ($statistikSiswa['sudah_ditangani'] ?? 0);
    $progressDitangani = round(($siswaDitangani / $totalSiswa) * 100);

    $mainCards = [
        [
            'label' => 'Total Siswa',
            'value' => $statistikSiswa['total_siswa'] ?? 0,
            'caption' => 'Dalam area tanggung jawab',
            'icon' => 'fa-user-graduate',
            'tone' => 'bg-indigo-600',
        ],
        [
            'label' => 'Total Penanganan',
            'value' => $statistikPenanganan['total'] ?? $summary['total'],
            'caption' => 'Sesuai periode terpilih',
            'icon' => 'fa-folder-open',
            'tone' => 'bg-blue-600',
        ],
        [
            'label' => 'Siswa Ditangani',
            'value' => $statistikSiswa['sudah_ditangani'] ?? 0,
            'caption' => $progressDitangani . '% dari total siswa',
            'icon' => 'fa-handshake-angle',
            'tone' => 'bg-teal-600',
        ],
        [
            'label' => 'Belum Ditangani',
            'value' => $statistikSiswa['belum_ditangani'] ?? 0,
            'caption' => 'Belum punya riwayat periode ini',
            'icon' => 'fa-user-clock',
            'tone' => 'bg-rose-500',
        ],
    ];

    $statusCards = [
        [
            'label' => 'Menunggu Respon',
            'value' => $summary['menunggu_respon'] ?? 0,
            'icon' => 'fa-clock',
            'class' => 'border-amber-100 bg-amber-50 text-amber-700',
        ],
        [
            'label' => 'Tindak Lanjut',
            'value' => $summary['menunggu_tindak_lanjut'] ?? 0,
            'icon' => 'fa-circle-info',
            'class' => 'border-sky-100 bg-sky-50 text-sky-700',
        ],
        [
            'label' => 'Selesai',
            'value' => $summary['selesai'] ?? 0,
            'icon' => 'fa-check-circle',
            'class' => 'border-emerald-100 bg-emerald-50 text-emerald-700',
        ],
        [
            'label' => 'Terlambat',
            'value' => $statistikPenanganan['terlambat'] ?? 0,
            'icon' => 'fa-triangle-exclamation',
            'class' => 'border-red-100 bg-red-50 text-red-700',
        ],
    ];
@endphp

<div class="mb-4 flex flex-col gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm md:flex-row md:items-center md:justify-between">
    <div class="flex items-center gap-2 text-xs text-slate-500">
        <i class="fas fa-calendar-alt text-indigo-500"></i>
        <span>Menampilkan data: <strong class="text-slate-800">{{ $periodeLabel }}</strong></span>
    </div>
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
        <span class="rounded-full bg-slate-100 px-3 py-1">Lunas: {{ number_format($statistikSiswa['lunas'] ?? 0, 0, ',', '.') }}</span>
        <span class="rounded-full bg-rose-50 px-3 py-1 text-rose-600">Belum Lunas: {{ number_format($statistikSiswa['belum_lunas'] ?? 0, 0, ',', '.') }}</span>
    </div>
</div>

<div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach ($mainCards as $card)
        <div class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="absolute -right-3 -top-3 opacity-5 transition-transform group-hover:scale-110">
                <i class="fas {{ $card['icon'] }} text-7xl"></i>
            </div>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $card['label'] }}</p>
                    <p class="mt-2 text-3xl font-black text-slate-800">{{ number_format($card['value'], 0, ',', '.') }}</p>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $card['tone'] }} text-white shadow-sm">
                    <i class="fas {{ $card['icon'] }}"></i>
                </div>
            </div>
            <p class="mt-3 text-xs font-semibold text-slate-500">{{ $card['caption'] }}</p>
        </div>
    @endforeach
</div>

<div class="mb-8 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach ($statusCards as $card)
        <div class="rounded-2xl border p-4 {{ $card['class'] }}">
            <div class="flex items-center justify-between gap-3">
                <span class="text-xs font-black uppercase tracking-wide">{{ $card['label'] }}</span>
                <i class="fas {{ $card['icon'] }}"></i>
            </div>
            <p class="mt-3 text-2xl font-black">{{ number_format($card['value'], 0, ',', '.') }}</p>
        </div>
    @endforeach
</div>
