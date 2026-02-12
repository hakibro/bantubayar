@extends('layouts.dashboard')

@section('content')
    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto px-4 py-6 md:p-8 pb-24">

        <div class="max-w-4xl mx-auto space-y-4">

            <button onclick="window.history.back()"
                class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-xl transition font-medium shadow-sm">
                <i class="fas fa-arrow-left text-sm"></i>
                <span>Kembali</span>
            </button>

            <!-- 1. Payment Focus Card (Updated Actions) -->
            <div class="bg-white rounded-3xl shadow-lg p-6 md:p-8 border border-gray-100 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-100 rounded-full blur-3xl opacity-50">
                </div>

                <!-- Main Card -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between relative z-10">
                    <!-- Main Info Card -->
                    <div class="flex justify-between items-start mb-6 md:mb-0 relative z-10">
                        <div class="flex-1">
                            @if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai')
                                <p
                                    class="mb-4 bg-yellow-300 inline-flex px-3 py-1 text-xs rounded-full font-bold text-gray-500">
                                    Sedang ditangani oleh:
                                    {{ $siswa->petugasPenangananAktif() }}
                                </p>
                            @endif
                            <h1 class="text-xl md:text-3xl font-bold flex flex-col   items-start gap-2 mb-4">
                                {{ $siswa->nama }}
                                <button onclick="openModal('detail')"
                                    class="text-primary text-sm font-normal hover:underline flex items-center  gap-1">
                                    <i class="fas fa-info-circle"></i> Info Siswa dan Pembayaran
                                </button>
                            </h1>
                            <p class="text-sm text-gray-500 font-medium mb-1">Total Tunggakan</p>

                            <h2
                                class="text-4xl font-bold text-accent tracking-tight
                            {{ $siswa->getTotalTunggakan() < 0 || $siswa->getKategoriBelumLunas() === null ? 'text-accent' : 'text-success' }}">
                                @if (is_null($siswa->getKategoriBelumLunas()))
                                    Belum Sinkron
                                @elseif ($siswa->getTotalTunggakan() < 0)
                                    Rp {{ number_format($siswa->getTotalTunggakan(), 0, ',', '.') }}
                                @else
                                    Lunas
                                @endif
                                <button onclick="syncPembayaran({{ $siswa->id }})"
                                    class="relative ml-1 mt-2 w-4 h-4 text-lg  text-blue-500 hover:text-blue-800 active:scale-95 transition-all duration-200">
                                    <i class="fas fa-sync absolute bottom-1"></i>
                                </button>
                            </h2>
                            @if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai')
                                <p class="text-sm text-gray-500 font-medium mt-2">Saat penanganan: Rp
                                    {{ number_format($penangananTerakhir->getTotalTunggakan(), 0, ',', '.') }}</p>
                            @endif
                            <div class="mt-4 pt-2 flex items-center gap-2 text-textMuted text-sm border-t border-gray-100">
                                <i class="fas fa-wallet text-gray-400"></i>
                                <span>Saldo saat ini:</span>
                                <span class="font-semibold text-gray-700"> Rp
                                    {{ number_format($siswa->saldoNominal, 0, ',', '.') }}
                                </span>
                            </div>
                            @if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai')
                                <div class="mt-1 flex items-center gap-2 text-textMuted text-sm">
                                    <span>Saat penanganan:</span>
                                    <span class="font-semibold text-gray-700"> Rp
                                        {{ number_format($penangananTerakhir?->saldo, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif


                            @if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai' && $penangananTerakhir->kesanggupanTerakhir)
                                @if ($penangananTerakhir->kesanggupanTerakhir->nominal)

                                    <div
                                        class="inline-flex flex-col py-2 px-4 bg-yellow-100 mt-2 rounded-lg text-xs text-gray-500 font-semibold">
                                        Sanggup membayar sebelum: {{ $penangananTerakhir->kesanggupanTerakhir->tanggal }}


                                        <span>
                                            Dengan nominal: Rp
                                            {{ number_format($penangananTerakhir->kesanggupanTerakhir->nominal, 0, ',', '.') }}
                                        </span>
                                        <!-- TODO: beri warna/simbol khusus jika kesanggupan wali kurang dari 1 hari -->


                                    </div>
                                @else
                                    <div
                                        class="inline-flex flex-col py-2 px-4 bg-red-400 mt-2 rounded-lg text-xs text-white font-semibold">
                                        Wali Belum Mengisi Nominal Kesanggupan. <br />
                                        Tanggal Kesanggupan: {{ $penangananTerakhir->kesanggupanTerakhir->tanggal }}
                                    </div>

                                @endif
                            @endif
                        </div>
                    </div>
                    <!-- Main Action Buttons -->
                    <!-- TODO: kembangkan pembatasan tindak lanjut dan hasil jika sudah diberi apresiasi -->
                    @if (
                        $penangananTerakhir &&
                            $penangananTerakhir->hasil === 'lunas' &&
                            $siswa->getTotalTunggakan() == 0 &&
                            $penangananTerakhir->updated_at->isSameMonth(now()))
                        <span class="text-gray-400 text-sm italic"> Sudah ditangani oleh
                            {{ $penangananTerakhir->petugas->name }}.</span>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-1 gap-3 md:gap-4 mt-6 md:mt-8">
                            <button onclick="openModal('{{ $siswa->phone ? 'action' : 'updatehp' }}')"
                                @if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai') @if ($penangananTerakhir->id_petugas !== auth()->id()) disabled
        title="Anda tidak berhak menindaklanjuti"
        class="opacity-50 cursor-not-allowed" @endif
                                @endif
                                class="w-full bg-primary hover:bg-blue-700 text-white
               py-3 px-6 md:py-4 rounded-2xl font-bold
               shadow-md shadow-blue-200
               transition active:scale-95
               flex items-center justify-center gap-2 text-sm md:text-base">
                                <i class="fas fa-tasks"></i>Tindak Lanjut
                            </button>

                            <button onclick="openModal('result')"
                                @if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai') @if ($penangananTerakhir->id_petugas !== auth()->id()) disabled
        title="Anda tidak berhak menindaklanjuti"
        class="opacity-50 cursor-not-allowed" @endif
                                @endif
                                class="w-full bg-white border-2 border-gray-300 text-gray-700
               hover:border-gray-400 hover:bg-gray-50
               py-3 px-6 md:py-4 rounded-2xl font-bold
               transition active:scale-95
               flex items-center justify-center gap-2 text-sm md:text-base">
                                <i class="fas fa-check-double"></i>
                                Hasil
                            </button>
                        </div>
                    @endif


                </div>

                <!-- Footer: Subtle History -->
                <div class="mt-8 pt-4 border-t border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Riwayat Aksi</h4>
                        {{-- <button onclick="openModal('detail')"
                            class="text-[10px] text-primary font-semibold hover:underline">Lihat Detail</button> --}}
                    </div>
                    <div class="space-y-3">
                        @if ($penangananTerakhir && $penangananTerakhir->status !== 'selesai')
                            @if ($riwayatAksi)
                                @foreach ($riwayatAksi as $aksi)
                                    <div class="flex items-start justify-between text-sm gap-3">
                                        <div class="flex items-start justify-center gap-3">
                                            <i
                                                class="text-xs mt-2 {{ $aksi->jenis_penanganan === 'chat' ? 'fas fa-comment text-green-500' : 'fas fa-phone text-blue-500' }}"></i>
                                            <span class="text-gray-600 font-medium leading-relaxed">
                                                {{ $aksi->catatan }}
                                            </span>
                                        </div>
                                        <span class="text-gray-400 text-xs whitespace-nowrap shrink-0">
                                            {{ $aksi->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-sm text-gray-400 italic">Belum ada riwayat aksi.</p>
                            @endif
                        @else
                            -
                        @endif
                    </div>
                </div>
            </div>
            <!-- 2. Recent Activity () -->
            <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Riwayat Penanganan</h3>
                </div>

                <div class="space-y-5" id="historyList">
                    @forelse ($siswa->penangananSelesai() as $riwayatPenanganan)
                        <details class="group border-t border-gray-200 pt-4 appearance-none">
                            {{-- Bagian Header (Klik untuk buka/tutup) --}}
                            <summary
                                class="flex items-center justify-between cursor-pointer list-none outline-none hover:bg-gray-50 p-2 rounded-lg transition-all">
                                <div class="flex items-center gap-4">
                                    <div>
                                        <p class="text-xs text-textMuted">Ditangani oleh:</p>
                                        <h4 class="font-bold text-md text-gray-800">
                                            {{ $riwayatPenanganan->petugas->name }}
                                        </h4>
                                        <p class="text-xs text-textMuted">
                                            {{ $riwayatPenanganan->created_at->diffForHumans() }}
                                        </p>

                                        {{-- Rating --}}
                                        <div class="flex items-center gap-0.5 mt-2">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i
                                                    class="fas fa-star text-[11px] {{ $i <= ($riwayatPenanganan->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                            @if (!is_null($riwayatPenanganan->rating))
                                                <span
                                                    class="text-[10px] text-textMuted ml-1">({{ $riwayatPenanganan->rating }}/5)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1 items-end text-right">
                                    <p
                                        class="text-xs px-2 py-0.5 rounded-full {{ in_array($riwayatPenanganan->hasil, ['lunas', 'isi_saldo', 'cicilan']) ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $riwayatPenanganan->hasil }}
                                    </p>
                                    <div
                                        class="font-bold text-sm {{ in_array($riwayatPenanganan->hasil, ['lunas', 'isi_saldo', 'cicilan']) ? 'text-success' : 'text-accent' }}">
                                        Rp {{ number_format($riwayatPenanganan->getTotalTunggakan(), 0, ',', '.') }}
                                    </div>

                                    {{-- Indikator Panah & Total Tindakan --}}
                                    <div class="flex items-center gap-2 mt-1 text-primary">
                                        <span class="text-xs font-semibold">{{ $riwayatPenanganan->histories()->count() }}
                                            tindakan</span>
                                        {{-- Icon berputar saat <details> terbuka berkat group-open:rotate-180 --}}
                                        <i
                                            class="fas fa-chevron-down text-[10px] transition-transform duration-300 group-open:rotate-180"></i>
                                    </div>
                                </div>
                            </summary>

                            {{-- Isi Detail (Muncul saat diklik) --}}
                            <div class="mt-4 ml-4 pl-4 border-l-2 border-gray-100 space-y-4 pb-2 animate-fadeIn">
                                @if (!is_null($riwayatPenanganan->catatan))
                                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Catatan Petugas:</p>
                                        <p class="text-sm text-gray-700 italic">"{{ $riwayatPenanganan->catatan }}"</p>
                                    </div>
                                @endif

                                <div class="mt-4">
                                    <p class="text-[10px] uppercase font-bold text-gray-400 mb-4 ml-1">Riwayat Tindakan</p>

                                    <div class="relative ml-20 border-l border-gray-100 space-y-3">
                                        @foreach ($riwayatPenanganan->histories as $history)
                                            <div class="relative pl-6">
                                                {{-- Waktu di luar garis (Kiri) --}}
                                                <div class="absolute -left-20 w-16 text-right top-1">
                                                    <p class="text-[9px] font-bold text-gray-500 leading-none">
                                                        {{ $history->created_at->format('d/m/y') }}</p>
                                                    <p class="text-[9px] text-gray-400 mt-1">
                                                        {{ $history->created_at->format('H:i') }}</p>
                                                </div>

                                                {{-- Dot Icon --}}
                                                <div
                                                    class="absolute -left-[11px] top-1 w-5 h-5 rounded-full bg-white border flex items-center justify-center shadow-sm
                {{ $history->jenis_penanganan === 'chat' ? 'border-green-500' : ($history->jenis_penanganan === 'phone' ? 'border-blue-500' : 'border-gray-300') }}">
                                                    <i
                                                        class="fas {{ $history->jenis_penanganan === 'chat' ? 'fa-comment' : ($history->jenis_penanganan === 'phone' ? 'fa-phone' : 'fa-info') }} 
                    text-[8px] {{ $history->jenis_penanganan === 'chat' ? 'text-green-500' : ($history->jenis_penanganan === 'phone' ? 'text-blue-500' : 'text-gray-400') }}"></i>
                                                </div>

                                                {{-- Content Minimalis --}}
                                                <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                                                    <p class="text-xs text-gray-700 leading-snug">
                                                        {{ $history->catatan ?? 'Tindakan terekam' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </details>
                    @empty
                        <div class="text-center py-10 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-100">
                            <i class="fas fa-history text-gray-200 text-4xl mb-3"></i>
                            <p class="text-sm text-gray-400 italic">Belum ada riwayat penanganan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>



    @push('scripts')
        <script>
            const formatCurrency = (num) => new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(num);

            // --- Modal Logic ---
            function openModal(type) {
                const modal = document.getElementById(`modal${type.charAt(0).toUpperCase() + type.slice(1)}`);
                const card = document.getElementById(`card${type.charAt(0).toUpperCase() + type.slice(1)}`);
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    card.classList.remove('translate-y-full');
                }, 10);
                if (type === 'detail') renderPaymentTab();
            }

            function closeModal(type) {
                const modal = document.getElementById(`modal${type.charAt(0).toUpperCase() + type.slice(1)}`);
                const card = document.getElementById(`card${type.charAt(0).toUpperCase() + type.slice(1)}`);
                modal.classList.add('opacity-0');
                card.classList.add('translate-y-full');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }

            document.querySelectorAll('[id^="modal"]').forEach(modal => {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        const type = modal.id.replace('modal', '').toLowerCase();
                        closeModal(type);
                    }
                });
            });

            function toggleAccordion(id) {
                const content = document.getElementById(`content${id}`);
                const icon = document.getElementById(`icon${id}`);
                content.classList.toggle('active');
                icon.classList.toggle('rotate-180');
            }

            document.addEventListener('DOMContentLoaded', () => {
                const hasSynced = localStorage.getItem('synced_siswa_{{ $siswa->id }}');

                if (!hasSynced) {
                    // Set flag agar tidak loop sebelum fungsi dipanggil
                    localStorage.setItem('synced_siswa_{{ $siswa->id }}', 'true');

                    // Panggil fungsi (pastikan nama sudah sama: syncPembayaran)
                    syncPembayaran({{ $siswa->id }});
                } else {
                    // Opsional: Hapus flag setelah beberapa saat jika ingin bisa sync lagi nanti
                    // localStorage.removeItem('synced_siswa_{{ $siswa->id }}');
                    console.log(localStorage.getItem('synced_siswa_{{ $siswa->id }}'));
                }
            });
        </script>
    @endpush

    <!-- --- MODALS --- -->
    @include('penanganan.partials.modal-action')
    @include('penanganan.partials.modal-updatehp')
    @include('penanganan.partials.modal-detail')
    @include('penanganan.partials.modal-result')

@endsection
