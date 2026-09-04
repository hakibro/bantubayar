@extends('layouts.dashboard')

@section('title', 'Pengaturan')

@section('content')
    <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-primary">Pengaturan</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">Pengaturan Aplikasi</h1>
                <p class="mt-2 text-sm text-gray-500">Kelola sinkronisasi data dan pengaturan lainnya.</p>
            </div>
        </div>

        {{-- Section: Sinkronisasi Pembayaran --}}
        <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Sinkronisasi Pembayaran</h2>
                    <p class="text-sm text-gray-500">Pembaruan ringkasan tunggakan siswa &amp; alumni.</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                    <i class="fas fa-rotate"></i>
                </span>
            </div>

            {{-- Penjelasan informatif --}}
            <div class="mb-5 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm leading-relaxed text-blue-900">
                <div class="flex items-start gap-3">
                    <i class="fas fa-circle-info mt-0.5 text-blue-500"></i>
                    <div>
                        <p class="font-semibold">Mengapa perlu sinkronisasi?</p>
                        <p class="mt-1">
                            Daftar siswa &amp; alumni menampilkan <strong>ringkasan tunggakan</strong> yang sudah
                            disinkronkan agar halaman tetap <strong>cepat</strong> saat memuat banyak data. Rincian
                            pembayaran setiap siswa/alumni <strong>selalu diambil langsung (realtime)</strong> dari
                            sistem pembayaran, sehingga angkanya tetap akurat.
                        </p>
                        <p class="mt-1">
                            Ringkasan ini diperbarui otomatis setiap <strong id="intervalLabel">{{ $intervalHours }}
                                jam</strong>. Anda dapat memicu pembaruan manual kapan pun atau mengubah jadwalnya di
                            bawah ini.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Info status --}}
            <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs uppercase text-gray-400">Terakhir Disinkronkan</p>
                    <p class="mt-1 text-lg font-bold text-gray-900" id="lastRefreshText">
                        {{ $lastRow->last_refresh ? \Carbon\Carbon::parse($lastRow->last_refresh)->diffForHumans() : 'Belum pernah' }}
                    </p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs uppercase text-gray-400">Jumlah Data Ringkasan</p>
                    <p class="mt-1 text-lg font-bold text-gray-900">{{ number_format($lastRow->total ?? 0) }}</p>
                </div>
                <div class="rounded-lg border border-gray-100 p-4">
                    <p class="text-xs uppercase text-gray-400">Status</p>
                    <p class="mt-1 text-lg font-bold text-gray-900" id="syncStatusText">
                        @if (isset($status['running']) && $status['running'])
                            <span class="text-amber-600">Sedang diproses…</span>
                        @else
                            <span class="text-emerald-600">Siap</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Aksi refresh manual --}}
            <div class="mb-5 flex flex-wrap items-center gap-3">
                <button id="refreshButton" type="button"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="fas fa-rotate"></i>
                    <span>Refresh Sekarang</span>
                </button>
                <p class="text-xs text-gray-400">Proses berjalan di latar belakang; halaman tidak akan tertahan.</p>
            </div>

            {{-- Pengaturan interval --}}
            <div class="rounded-lg border border-gray-100 p-4">
                <p class="mb-3 text-sm font-semibold text-gray-700">Jadwal Sinkronisasi Otomatis</p>
                <form id="intervalForm" class="flex flex-wrap items-center gap-3">
                    @csrf
                    <select name="sync_interval_hours"
                        class="h-11 rounded-xl border border-gray-200 bg-slate-50 px-3 text-sm text-gray-700 outline-none focus:border-blue-500">
                        <option value="6" {{ $intervalHours == 6 ? 'selected' : '' }}>Setiap 6 jam</option>
                        <option value="12" {{ $intervalHours == 12 ? 'selected' : '' }}>Setiap 12 jam</option>
                    </select>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        <i class="fas fa-save"></i>
                        <span>Simpan Jadwal</span>
                    </button>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function refreshStatus() {
                $.ajax({
                    url: "{{ route('admin.pengaturan.status') }}",
                    method: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        if (res.running) {
                            $('#syncStatusText').html('<span class="text-amber-600">Sedang diproses…</span>');
                            $('#refreshButton').prop('disabled', true);
                        } else {
                            $('#syncStatusText').html('<span class="text-emerald-600">Siap</span>');
                            $('#refreshButton').prop('disabled', false);
                            if (res.cache_last_refresh) {
                                $('#lastRefreshText').text(res.cache_last_refresh);
                            }
                        }
                    }
                });
            }

            $('#refreshButton').on('click', function() {
                const btn = $(this);
                btn.prop('disabled', true).find('span').text('Memproses…');

                $.ajax({
                    url: "{{ route('admin.pengaturan.refresh') }}",
                    method: 'POST',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    success: function(res) {
                        showToast(res.message, 'success');
                        refreshStatus();
                        // Polling sampai selesai.
                        let tries = 0;
                        const poll = setInterval(function() {
                            $.ajax({
                                url: "{{ route('admin.pengaturan.status') }}",
                                method: 'GET',
                                dataType: 'json',
                                success: function(r) {
                                    if (!r.running) {
                                        clearInterval(poll);
                                        showToast('Sinkronisasi selesai.',
                                            'success');
                                        location.reload();
                                    }
                                }
                            });
                            if (++tries > 120) {
                                clearInterval(poll);
                            }
                        }, 5000);
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message : 'Terjadi kesalahan';
                        showToast(msg, 'error');
                        btn.prop('disabled', false).find('span').text('Refresh Sekarang');
                    }
                });
            });

            $('#intervalForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('admin.pengaturan.updateInterval') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        showToast(res.message, 'success');
                        const val = $('#intervalForm select[name="sync_interval_hours"]').val();
                        $('#intervalLabel').text(val + ' jam');
                    },
                    error: function() {
                        showToast('Gagal menyimpan jadwal', 'error');
                    }
                });
            });

            // Cek status awal saat halaman dibuka.
            refreshStatus();
        });
    </script>
@endpush
