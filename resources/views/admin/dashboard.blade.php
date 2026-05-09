@extends('layouts.dashboard')

@section('content')
    @php
        $monthLabel = now()->translatedFormat('F Y');
        $paymentCards = [
            [
                'label' => 'Total Siswa',
                'value' => $totalSiswa,
                'caption' => 'Seluruh data siswa aktif',
                'icon' => 'fa-users',
                'color' => 'text-blue-600',
                'bg' => 'bg-blue-50',
                'border' => 'border-blue-200',
            ],
            [
                'label' => 'Siswa Lunas',
                'value' => $siswaLunas,
                'caption' => $persentaseLunas . '% dari total siswa',
                'icon' => 'fa-circle-check',
                'color' => 'text-emerald-600',
                'bg' => 'bg-emerald-50',
                'border' => 'border-emerald-200',
            ],
            [
                'label' => 'Belum Lunas',
                'value' => $siswaBelumLunas,
                'caption' => $persentaseBelumLunas . '% perlu dipantau',
                'icon' => 'fa-triangle-exclamation',
                'color' => 'text-rose-600',
                'bg' => 'bg-rose-50',
                'border' => 'border-rose-200',
            ],
            [
                'label' => 'Penanganan Aktif',
                'value' => $penangananAktif,
                'caption' => 'Menunggu respon/tindak lanjut',
                'icon' => 'fa-headset',
                'color' => 'text-amber-600',
                'bg' => 'bg-amber-50',
                'border' => 'border-amber-200',
            ],
        ];

        $teamCards = [
            ['label' => 'Petugas', 'value' => $totalPetugas, 'icon' => 'fa-user-shield', 'class' => 'text-indigo-600 bg-indigo-50'],
            ['label' => 'Bendahara', 'value' => $totalBendahara, 'icon' => 'fa-calculator', 'class' => 'text-cyan-700 bg-cyan-50'],
            ['label' => 'Home Visit Aktif', 'value' => $homeVisitAktif, 'icon' => 'fa-house-user', 'class' => 'text-teal-700 bg-teal-50'],
            ['label' => 'Home Visit Bulan Ini', 'value' => $homeVisitBulanIni, 'icon' => 'fa-calendar-check', 'class' => 'text-fuchsia-700 bg-fuchsia-50'],
        ];

        $quickLinks = [
            ['label' => 'Data Siswa', 'route' => 'admin.siswa.index', 'icon' => 'fa-user-graduate', 'text' => 'Cari dan lihat status pembayaran siswa.'],
            ['label' => 'Pembayaran', 'route' => 'admin.pembayaran-siswa.index', 'icon' => 'fa-money-bill-wave', 'text' => 'Pantau data pembayaran siswa.'],
            ['label' => 'Kelola Petugas', 'route' => 'admin.petugas.index', 'icon' => 'fa-user-shield', 'text' => 'Atur akun dan akses petugas.'],
            ['label' => 'Assign Petugas', 'route' => 'admin.assign.index', 'icon' => 'fa-link', 'text' => 'Bagikan siswa ke petugas.'],
            ['label' => 'Home Visit', 'route' => 'admin.home-visit.select', 'icon' => 'fa-house-chimney-medical', 'text' => 'Buat kunjungan rumah baru.'],
            ['label' => 'Laporan Petugas', 'route' => 'admin.laporan.petugas', 'icon' => 'fa-chart-line', 'text' => 'Evaluasi progres petugas.'],
        ];
    @endphp

    <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-primary">Dashboard Admin</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">Ringkasan Operasional Pembayaran</h1>
                <p class="mt-2 text-sm text-gray-500">Pantauan siswa, penanganan, dan home visit periode {{ $monthLabel }}.</p>
            </div>
            <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="fas fa-calendar-day"></i>
                </span>
                <div>
                    <p class="text-xs font-semibold uppercase text-gray-400">Hari ini</p>
                    <p class="text-sm font-semibold text-gray-800">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($paymentCards as $card)
                <div class="rounded-lg border {{ $card['border'] }} bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
                        </div>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $card['bg'] }} {{ $card['color'] }}">
                            <i class="fas {{ $card['icon'] }} text-lg"></i>
                        </span>
                    </div>
                    <p class="mt-4 text-sm text-gray-500">{{ $card['caption'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Komposisi Pembayaran</h2>
                        <p class="text-sm text-gray-500">Perbandingan siswa lunas dan belum lunas.</p>
                    </div>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                        {{ number_format($totalSiswa) }} siswa
                    </span>
                </div>

                <div class="mb-5 h-4 overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ min($persentaseLunas, 100) }}%"></div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg bg-emerald-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-emerald-800">Lunas</p>
                            <p class="text-sm font-bold text-emerald-700">{{ $persentaseLunas }}%</p>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($siswaLunas) }}</p>
                    </div>
                    <div class="rounded-lg bg-rose-50 p-4">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-rose-800">Belum Lunas</p>
                            <p class="text-sm font-bold text-rose-700">{{ $persentaseBelumLunas }}%</p>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($siswaBelumLunas) }}</p>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Tim & Kunjungan</h2>
                <p class="mb-4 text-sm text-gray-500">Kapasitas pengguna dan aktivitas home visit.</p>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    @foreach ($teamCards as $card)
                        <div class="flex items-center justify-between rounded-lg border border-gray-100 p-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg {{ $card['class'] }}">
                                    <i class="fas {{ $card['icon'] }}"></i>
                                </span>
                                <p class="text-sm font-semibold text-gray-600">{{ $card['label'] }}</p>
                            </div>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Penanganan Bulan Ini</h2>
                <p class="mb-5 text-sm text-gray-500">Dilihat dari status pembayaran siswa.</p>

                <div class="space-y-4">
                    <div class="rounded-lg border border-emerald-100 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="font-semibold text-gray-800">Siswa Lunas</p>
                            <i class="fas fa-circle-check text-emerald-500"></i>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs uppercase text-gray-400">Aktif</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($penangananPembayaran['lunas']['aktif']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-gray-400">Selesai</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($penangananPembayaran['lunas']['selesai']) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-rose-100 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <p class="font-semibold text-gray-800">Belum Lunas</p>
                            <i class="fas fa-circle-exclamation text-rose-500"></i>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-xs uppercase text-gray-400">Aktif</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($penangananPembayaran['belum_lunas']['aktif']) }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase text-gray-400">Selesai</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($penangananPembayaran['belum_lunas']['selesai']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-gray-50 p-4">
                    <p class="text-sm text-gray-500">Total selesai bulan ini</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($penangananSelesaiBulanIni) }}</p>
                </div>
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Tautan Cepat</h2>
                        <p class="text-sm text-gray-500">Akses menu yang paling sering dipakai admin.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($quickLinks as $link)
                        <a href="{{ route($link['route']) }}"
                            class="group rounded-lg border border-gray-200 p-4 transition hover:border-primary/40 hover:bg-primary/5">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 transition group-hover:bg-primary group-hover:text-white">
                                    <i class="fas {{ $link['icon'] }}"></i>
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $link['label'] }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ $link['text'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection
