@extends('layouts.dashboard')

@section('title', 'Detail Siswa')

@section('content')
    @php
        $isLunas = (bool) $siswa->is_lunas;
        $totalDibayar = collect($summary)->sum('total_debet');
        $totalTagihan = collect($summary)->sum('total_kredit');
        $groupedBelumLunas = collect($belumLunas)->groupBy('idperiode');
        $backUrl = url()->previous() !== url()->current() ? url()->previous() : route('admin.siswa.index');
        $statusClass = [
            'selesai' => 'bg-emerald-100 text-emerald-700',
            'menunggu_respon' => 'bg-amber-100 text-amber-700',
            'menunggu_tindak_lanjut' => 'bg-blue-100 text-blue-700',
        ];
    @endphp

    <div class="min-h-screen bg-slate-50 px-3 py-4 md:p-8">
        <div class="mx-auto max-w-6xl space-y-4 md:space-y-5">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ $backUrl }}"
                    class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm hover:border-indigo-200 hover:text-indigo-700">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Kembali
                </a>

                <span class="rounded-full px-4 py-2 text-xs font-black {{ $isLunas ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                    {{ $isLunas ? 'Lunas' : 'Belum Lunas' }}
                </span>
            </div>

            <details open class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 md:px-7 md:py-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-500">Profil</p>
                        <h2 class="text-base font-black text-slate-800 md:text-lg">Data Siswa dan Info Pembayaran</h2>
                    </div>
                    <i class="fas fa-chevron-down text-sm text-slate-400 transition group-open:rotate-180"></i>
                </summary>
                <div class="grid gap-0 lg:grid-cols-[1.3fr_.9fr]">
                    <div class="p-4 md:p-7">
                        @if ($penangananAktif)
                            <div class="mb-3 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                Sedang ditangani oleh {{ $penangananAktif->petugas?->name ?? 'Petugas' }}
                            </div>
                        @endif

                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-500">Detail Siswa</p>
                        <h1 class="mt-1 text-xl font-black text-slate-900 md:mt-2 md:text-4xl">{{ $siswa->nama }}</h1>
                        <p class="mt-1 font-mono text-sm font-bold text-slate-400">{{ $siswa->idperson }}</p>

                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs md:mt-5 md:gap-3 md:text-sm">
                            <div class="rounded-2xl bg-slate-50 p-3 md:p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Formal</p>
                                <p class="mt-1 font-bold text-slate-700">{{ $siswa->unit_formal ?? '-' }} - {{ $siswa->kelas_formal ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 md:p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Pondok</p>
                                <p class="mt-1 font-bold text-slate-700">{{ $siswa->AsramaPondok ?? '-' }} - {{ $siswa->KamarPondok ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 md:p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Diniyah</p>
                                <p class="mt-1 font-bold text-slate-700">{{ $siswa->TingkatMadin ?? '-' }} - {{ $siswa->KelasMadin ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 md:p-4">
                                <p class="text-xs font-bold uppercase text-slate-400">Saldo</p>
                                <p class="mt-1 font-black text-slate-700">Rp {{ number_format($siswa->saldoNominal, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-900 p-4 text-white md:p-7 lg:border-l lg:border-t-0">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-200">Info Pembayaran</p>
                            <button type="button" onclick="openPaymentModal()"
                                class="rounded-full bg-white/10 px-3 py-1 text-xs font-black text-white ring-1 ring-white/15 hover:bg-white/15">
                                Detail
                            </button>
                        </div>
                        <div class="mt-4 md:mt-5">
                            <p class="text-sm font-semibold text-slate-300">Total Tunggakan</p>
                            <p class="mt-1 text-2xl font-black md:text-3xl {{ $totalTunggakan > 0 ? 'text-rose-300' : 'text-emerald-300' }}">
                                {{ $totalTunggakan > 0 ? 'Rp ' . number_format($totalTunggakan, 0, ',', '.') : 'Lunas' }}
                            </p>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-2 md:mt-6 md:gap-3">
                            <div class="rounded-2xl bg-white/10 p-3 md:p-4">
                                <p class="text-xs font-bold text-slate-300">Tagihan</p>
                                <p class="mt-1 text-sm font-black">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-3 md:p-4">
                                <p class="text-xs font-bold text-slate-300">Dibayar</p>
                                <p class="mt-1 text-sm font-black">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-3 md:p-4">
                                <p class="text-xs font-bold text-slate-300">Item Belum Lunas</p>
                                <p class="mt-1 text-sm font-black">{{ count($belumLunas) }} item</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-3 md:p-4">
                                <p class="text-xs font-bold text-slate-300">Penanganan</p>
                                <p class="mt-1 text-sm font-black">{{ $penangananList->count() }} kali</p>
                            </div>
                        </div>
                    </div>
                </div>
            </details>

            <details open class="group rounded-[2rem] border border-slate-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-5 py-4 md:px-6">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-500">Penanganan</p>
                        <h2 class="mt-1 text-xl font-black text-slate-800">Riwayat Penanganan dan Aksi</h2>
                    </div>
                    <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-black text-indigo-600">
                        {{ $penangananList->count() }} penanganan
                    </span>
                    <i class="fas fa-chevron-down text-sm text-slate-400 transition group-open:rotate-180"></i>
                </summary>

                <div class="space-y-4 px-5 pb-5 md:px-6 md:pb-6">
                    @forelse ($penangananList as $penanganan)
                        @php
                            $isActive = $penanganan->status !== 'selesai';
                            $badgeClass = $statusClass[$penanganan->status] ?? 'bg-slate-100 text-slate-700';
                            $histories = $penanganan->histories;
                        @endphp

                        <details class="group rounded-2xl border {{ $isActive ? 'border-amber-200 bg-amber-50/60' : 'border-slate-100 bg-slate-50' }} p-4" {{ $loop->first ? 'open' : '' }}>
                            <summary class="cursor-pointer list-none">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $badgeClass }}">
                                                {{ Str::title(str_replace('_', ' ', $penanganan->status ?? '-')) }}
                                            </span>
                                            @if ($penanganan->hasil)
                                                <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                                                    Hasil: {{ Str::title(str_replace('_', ' ', $penanganan->hasil)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="mt-3 font-black text-slate-800">
                                            {{ $penanganan->petugas?->name ?? 'Petugas tidak diketahui' }}
                                        </p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400">
                                            Dibuat {{ $penanganan->created_at?->format('d M Y H:i') }} - Diperbarui {{ $penanganan->updated_at?->diffForHumans() }}
                                        </p>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <p class="text-xs font-bold uppercase text-slate-400">Saat Ditangani</p>
                                        <p class="text-sm font-black {{ $penanganan->getTotalTunggakan() > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                            Rp {{ number_format($penanganan->getTotalTunggakan(), 0, ',', '.') }}
                                        </p>
                                        <div class="mt-2 flex items-center justify-end gap-2 text-xs font-bold text-indigo-600">
                                            <span>{{ $histories->count() }} aksi</span>
                                            <i class="fas fa-chevron-down transition group-open:rotate-180"></i>
                                        </div>
                                    </div>
                                </div>
                            </summary>

                            <div class="mt-4 border-t border-slate-200/80 pt-4">
                                <div class="grid gap-3 text-xs sm:grid-cols-3">
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="font-bold text-slate-400">Saldo Saat Penanganan</p>
                                        <p class="mt-1 font-black text-slate-700">Rp {{ number_format($penanganan->saldo ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="font-bold text-slate-400">Kesanggupan</p>
                                        <p class="mt-1 font-black text-slate-700">
                                            {{ $penanganan->kesanggupanTerakhir?->tanggal ?? '-' }}
                                        </p>
                                        @if ($penanganan->kesanggupanTerakhir?->nominal)
                                            <p class="mt-1 text-slate-500">Rp {{ number_format($penanganan->kesanggupanTerakhir->nominal, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                    <div class="rounded-2xl bg-white p-3 ring-1 ring-slate-100">
                                        <p class="font-bold text-slate-400">Catatan Hasil</p>
                                        <p class="mt-1 font-semibold text-slate-700">{{ $penanganan->catatan ?: '-' }}</p>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <p class="mb-3 text-xs font-black uppercase tracking-wide text-slate-400">History Aksi</p>
                                    <div class="space-y-3">
                                        @forelse ($histories as $history)
                                            @php
                                                $icon = $history->jenis_penanganan === 'chat'
                                                    ? ['fa-comment', 'text-emerald-500', 'bg-emerald-50']
                                                    : ($history->jenis_penanganan === 'phone'
                                                        ? ['fa-phone', 'text-blue-500', 'bg-blue-50']
                                                        : ['fa-info', 'text-slate-500', 'bg-slate-100']);
                                            @endphp
                                            <div class="flex gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-100">
                                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $icon[2] }}">
                                                    <i class="fas {{ $icon[0] }} {{ $icon[1] }} text-xs"></i>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                                        <p class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                            {{ Str::title(str_replace('_', ' ', $history->jenis_penanganan)) }}
                                                        </p>
                                                        <p class="text-xs font-semibold text-slate-400">
                                                            {{ $history->created_at?->format('d M Y H:i') }}
                                                        </p>
                                                    </div>
                                                    <p class="mt-1 text-sm leading-relaxed text-slate-700">
                                                        {{ $history->catatan ?: 'Tindakan terekam.' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rounded-2xl border border-dashed border-slate-200 p-6 text-center text-sm font-semibold text-slate-400">
                                                Belum ada history aksi.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </details>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-10 text-center">
                            <i class="fas fa-history text-4xl text-slate-200"></i>
                            <p class="mt-3 text-sm font-bold text-slate-400">Belum ada penanganan untuk siswa ini.</p>
                        </div>
                    @endforelse
                </div>
            </details>
        </div>
    </div>

    <div id="paymentDetailModal" class="fixed inset-0 z-[70] hidden bg-slate-950/60 p-3 backdrop-blur-sm">
        <div class="mx-auto flex h-full max-w-5xl items-end md:items-center">
            <div class="max-h-[88vh] w-full overflow-hidden rounded-t-[2rem] bg-white shadow-2xl md:rounded-[2rem]">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-4 md:px-6">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-500">Pembayaran</p>
                        <h2 class="text-lg font-black text-slate-800">Ringkasan dan Tunggakan</h2>
                    </div>
                    <button type="button" onclick="closePaymentModal()"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="max-h-[calc(88vh-76px)] overflow-y-auto p-4 md:p-6">
                    <div class="grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
                        <section class="rounded-3xl border border-slate-200 bg-white p-4 md:p-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-500">Periode</p>
                                <h3 class="mt-1 text-lg font-black text-slate-800">Ringkasan Per Periode</h3>
                            </div>

                            <div class="mt-4 space-y-3">
                                @forelse ($summary as $sum)
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-black text-slate-800">Periode {{ $sum->idperiode }}</p>
                                                @if ($sum->kelas_history)
                                                    <p class="mt-1 text-xs text-slate-500">{{ $sum->kelas_history }}</p>
                                                @endif
                                            </div>
                                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $sum->lunas ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $sum->lunas ? 'Lunas' : 'Belum' }}
                                            </span>
                                        </div>

                                        <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                                            <div>
                                                <p class="font-bold text-slate-400">Tagihan</p>
                                                <p class="mt-1 font-black text-slate-700">Rp {{ number_format($sum->total_kredit, 0, ',', '.') }}</p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-400">Dibayar</p>
                                                <p class="mt-1 font-black text-slate-700">Rp {{ number_format($sum->total_debet, 0, ',', '.') }}</p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-400">Sisa</p>
                                                <p class="mt-1 font-black {{ $sum->sisa_tagihan > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                                    Rp {{ number_format($sum->sisa_tagihan, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm font-semibold text-slate-400">
                                        Tidak ada data pembayaran.
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        <section class="rounded-3xl border border-slate-200 bg-white p-4 md:p-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-rose-500">Tunggakan</p>
                                <h3 class="mt-1 text-lg font-black text-slate-800">Detail Belum Lunas</h3>
                            </div>

                            <div class="mt-4 space-y-4">
                                @forelse ($groupedBelumLunas as $periode => $items)
                                    <details class="group rounded-2xl border border-slate-100 bg-slate-50 p-4" {{ $loop->first ? 'open' : '' }}>
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3">
                                            <div>
                                                <p class="font-black text-slate-800">Periode {{ $periode }}</p>
                                                <p class="text-xs font-semibold text-slate-400">{{ $items->count() }} item belum lunas</p>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <p class="text-sm font-black text-rose-600">Rp {{ number_format($items->sum('selisih'), 0, ',', '.') }}</p>
                                                <i class="fas fa-chevron-down text-xs text-slate-400 transition group-open:rotate-180"></i>
                                            </div>
                                        </summary>

                                        <div class="mt-4 divide-y divide-slate-200/70">
                                            @foreach ($items as $item)
                                                <div class="py-3">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p class="text-sm font-bold text-slate-700">{{ $item->judul }}</p>
                                                            <p class="text-xs text-slate-400">{{ $item->nama_unit }}</p>
                                                        </div>
                                                        <p class="text-sm font-black text-rose-600">Rp {{ number_format($item->selisih, 0, ',', '.') }}</p>
                                                    </div>
                                                    <div class="mt-2 flex flex-wrap gap-3 text-[11px] font-semibold text-slate-400">
                                                        <span>Tagihan Rp {{ number_format($item->jml_kredit, 0, ',', '.') }}</span>
                                                        <span>Dibayar Rp {{ number_format($item->jml_debet, 0, ',', '.') }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @empty
                                    <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50 p-8 text-center">
                                        <i class="fas fa-check-circle text-3xl text-emerald-500"></i>
                                        <p class="mt-3 text-sm font-bold text-emerald-700">Tidak ada tunggakan aktif.</p>
                                    </div>
                                @endforelse
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openPaymentModal() {
                document.getElementById('paymentDetailModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closePaymentModal() {
                document.getElementById('paymentDetailModal').classList.add('hidden');
                document.body.style.overflow = '';
            }

            document.getElementById('paymentDetailModal')?.addEventListener('click', function(event) {
                if (event.target === this) {
                    closePaymentModal();
                }
            });
        </script>
    @endpush
@endsection
