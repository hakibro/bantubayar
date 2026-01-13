@extends('layouts.dashboard')
@section('title', 'Riwayat Penanganan')

@section('content')
    <!-- Scrollable Content -->
    <div class="flex-1 overflow-y-auto p-4 md:p-8 pb-24">

        <div class="max-w-4xl mx-auto space-y-6">

            <!-- 1. Payment Focus Card (Updated Actions) -->
            <div class="bg-white rounded-3xl shadow-lg p-6 md:p-8 border border-gray-100 relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-red-100 rounded-full blur-3xl opacity-50">
                </div>

                <div class="flex justify-between items-start mb-6 relative z-10">
                    <div class="flex-1">
                        @if ($siswa->petugasPenangananAktif())
                            <p class="mb-4 bg-yellow-300 inline-flex px-3 py-1 text-xs rounded-full font-bold text-gray-500">
                                Sedang
                                ditangani oleh:
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
                        <!-- Total Belum Lunas -->
                        @php
                            $totalBelumLunas = 0;

                            $belumLunas = $siswa->getKategoriBelumLunas();

                            if (is_array($belumLunas)) {
                                foreach ($belumLunas as $kategori) {
                                    foreach ($kategori['items'] as $item) {
                                        $totalBelumLunas += $item['remaining_balance'] ?? 0;
                                    }
                                }
                            }
                        @endphp
                        <h2
                            class="text-4xl font-bold text-accent tracking-tight
                        {{ $totalBelumLunas < 0 ? 'text-accent' : 'text-success' }}">

                            @if ($totalBelumLunas < 0)
                                Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}
                            @else
                                Lunas
                            @endif
                        </h2>

                        <div class="mt-3 flex items-center gap-2 text-textMuted text-sm">
                            <i class="fas fa-wallet text-gray-400"></i>
                            <span>Saldo saat ini:</span>
                            <span class="font-semibold text-gray-700"> Rp
                                {{ number_format($siswa->saldo?->saldo, 0, ',', '.') }}
                            </span>
                        </div>


                    </div>
                </div>

                <!-- Main Action Buttons -->
                <div class="grid grid-cols-2 gap-4 mt-8">
                    <button onclick="openModal('{{ $siswa->phone ? 'action' : 'updatehp' }}')"
                        class="bg-primary hover:bg-blue-700 text-white py-4 rounded-2xl font-bold shadow-md shadow-blue-200 transition active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-tasks"></i> Tindak Lanjut
                    </button>
                    <button onclick="openModal('result')"
                        class="bg-white border-2 border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50 py-4 rounded-2xl font-bold transition active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-check-double"></i> Hasil
                    </button>
                </div>

                <!-- Footer: Subtle History -->
                <div class="mt-8 pt-4 border-t border-gray-100">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Riwayat Aksi</h4>
                        <button onclick="openModal('detail')"
                            class="text-[10px] text-primary font-semibold hover:underline">Lihat Detail</button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-[10px]">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <span class="text-gray-600 font-medium">Dihubungi via WA</span>
                            </div>
                            <span class="text-gray-400 text-xs">10 Okt</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-[10px]">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <span class="text-gray-600 font-medium">Telepon</span>
                            </div>
                            <span class="text-gray-400 text-xs">08 Okt</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 2. Recent Activity (Payments) -->
            <div class="bg-white rounded-3xl shadow-sm p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold">Riwayat Penanganan</h3>
                    <button onclick="openModal('detail')" class="text-xs text-primary font-semibold hover:underline">Lihat
                        Semua</button>
                </div>

                <div class="space-y-5" id="historyList">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-800">SPP Oktober 2023</h4>
                                <p class="text-xs text-textMuted">Jatuh tempo: 10 Okt</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-accent text-sm">Rp 500.000</div>
                            <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Belum
                                Bayar</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-gray-800">SPP September 2023</h4>
                                <p class="text-xs text-textMuted">Jatuh tempo: 10 Sep</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="font-bold text-accent text-sm">Rp 500.000</div>
                            <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full">Belum
                                Bayar</span>
                        </div>
                    </div>
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
                console.log(content, icon);
                content.classList.toggle('active');
                icon.classList.toggle('rotate-180');
            }
        </script>
    @endpush

    <!-- --- MODALS --- -->
    @include('penanganan.partials.modal-action')
    @include('penanganan.partials.modal-updatehp')
    @include('penanganan.partials.modal-result')
    @include('penanganan.partials.modal-detail')
@endsection
