@extends('layouts.container')

@section('content')
    <div class="min-h-screen bg-[#F8F9FA] pb-12 font-sans">

        <div class="bg-white px-6 pt-8 pb-6 rounded-b-[40px] shadow-sm border-b border-gray-100">
            <div class="max-w-md mx-auto">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h1 class="text-2xl font-black text-gray-900 leading-tight">Tagihan<br>Siswa</h1>
                        <div class="h-1 w-8 bg-red-500 mt-2 rounded-full"></div>
                    </div>
                    <div
                        class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        Belum Lunas
                    </div>
                </div>

                <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-2xl">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center text-primary">
                        <i class="fas fa-user-graduate text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">{{ $siswa->nama }}</h2>
                        <p class="text-xs text-gray-500">ID: {{ $siswa->idperson }} • {{ $siswa->UnitFormal }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-md mx-auto px-6 -mt-4">
            <div
                class="bg-gradient-to-br from-gray-900 to-gray-800 rounded-3xl p-6 shadow-xl text-white relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/5 rounded-full"></div>

                <p class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mb-1">Total Kewajiban Pembayaran</p>
                <h3 id="grandTotalRemaining" class="text-3xl font-black tabular-nums">Rp 0</h3>

                <div class="mt-6 flex justify-between items-center bg-white/10 rounded-xl p-3 backdrop-blur-md">
                    <span class="text-[10px] text-gray-300">Data berdasarkan tagihan aktif</span>
                    <i class="fas fa-wallet text-xs text-gray-400"></i>
                </div>
            </div>

            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider">Rincian Per Periode</h4>
                    <span id="categoryCount"
                        class="text-[10px] bg-gray-200 text-gray-600 px-2 py-0.5 rounded-md font-bold">0 Item</span>
                </div>

                <div id="groupedList" class="space-y-8">
                </div>
            </div>

            <div class="mt-10 bg-indigo-50 border border-indigo-100 rounded-3xl p-6 text-center">
                <div
                    class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center mx-auto mb-3 shadow-lg shadow-indigo-200 rotate-3">
                    <i class="fas fa-mobile-alt text-lg"></i>
                </div>
                <h5 class="text-sm font-bold text-indigo-900">Pembayaran Lebih Mudah</h5>
                <p class="text-[11px] text-indigo-700 mt-1 leading-relaxed">
                    Gunakan aplikasi <strong>Ngalah Mobile</strong> untuk melakukan pembayaran secara instan, cek riwayat,
                    dan mendapatkan bukti bayar digital.
                </p>

                <button onclick="openNgalahMobile()"
                    class="mt-4 inline-flex items-center gap-2 bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-xs font-bold hover:bg-indigo-700 transition shadow-md active:scale-95">
                    <i class="fab fa-google-play"></i>
                    Buka Ngalah Mobile
                </button>
            </div>
        </div>
    </div>

    <style>
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0, 1, 0, 1);
        }

        .accordion-content.open {
            max-height: 1000px;
            transition: all 0.3s cubic-bezier(1, 0, 1, 0);
        }

        .text-primary {
            color: #2563eb;
        }
    </style>
@endsection

@push('scripts')
    <script>
        const paymentData = @json($pembayaran ?? []);

        function formatCurrency(val) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(Math.abs(val));
        }

        function toggleAccordion(id) {
            const content = document.getElementById('content' + id);
            const icon = document.getElementById('icon' + id);
            content.classList.toggle('open');
            icon.classList.toggle('rotate-180');
        }

        function renderView() {
            const listContainer = document.getElementById('groupedList');
            const grandTotalEl = document.getElementById('grandTotalRemaining');
            const countEl = document.getElementById('categoryCount');

            if (!paymentData || paymentData.length === 0) {
                listContainer.innerHTML =
                    `<div class="text-center py-12 bg-white rounded-3xl shadow-sm border border-gray-100 italic text-gray-400 text-sm">Tidak ada tagihan aktif.</div>`;
                return;
            }

            // 1. Grouping Data by Periode
            const grouped = paymentData.reduce((acc, item) => {
                if (!acc[item.periode]) acc[item.periode] = [];
                acc[item.periode].push(item);
                return acc;
            }, {});

            let totalHutang = 0;
            let totalItems = 0;
            listContainer.innerHTML = '';

            // 2. Render Grouped Data
            Object.keys(grouped).sort().reverse().forEach((periode, gIdx) => {
                const periodeSection = document.createElement('div');
                periodeSection.className = "space-y-3";

                // Label Periode
                periodeSection.innerHTML = `
                    <div class="flex items-center gap-2 px-1">
                        <div class="h-px bg-gray-200 flex-1"></div>
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest px-2">${periode}</span>
                        <div class="h-px bg-gray-200 flex-1"></div>
                    </div>
                `;

                const categoryContainer = document.createElement('div');
                categoryContainer.className = "space-y-3";

                grouped[periode].forEach((cat, cIdx) => {
                    const uniqueId = `g${gIdx}c${cIdx}`;
                    totalHutang += Math.abs(cat.summary.total_remaining);
                    totalItems++;

                    const card = document.createElement('div');
                    card.className =
                        "bg-white rounded-[24px] border border-gray-100 shadow-sm overflow-hidden";
                    card.innerHTML = `
                        <div class="p-5 flex justify-between items-center cursor-pointer active:bg-gray-50 transition" onclick="toggleAccordion('${uniqueId}')">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center text-sm shadow-inner">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-gray-800">${cat.category_name}</h4>
                                    <p class="text-[9px] text-gray-400">Klik untuk rincian</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black text-red-600">${formatCurrency(cat.summary.total_remaining)}</span>
                                <i id="icon${uniqueId}" class="fas fa-chevron-down text-gray-300 text-[10px] transition-transform duration-300"></i>
                            </div>
                        </div>
                        <div id="content${uniqueId}" class="accordion-content bg-gray-50/50">
                            <div class="px-5 pb-5 pt-2 space-y-3">
                                <div class="h-px bg-gray-100 w-full mb-3"></div>
                                ${cat.items.map(item => `
                                                                            <div class="flex justify-between items-center">
                                                                                <div>
                                                                                    <p class="text-[10px] font-bold text-gray-700">${item.unit_name}</p>
                                                                                    <p class="text-[8px] text-gray-400 italic">Jatuh Tempo: ${item.journal_date}</p>
                                                                                </div>
                                                                                <span class="text-[10px] font-black text-gray-600">${formatCurrency(item.remaining_balance)}</span>
                                                                            </div>
                                                                        `).join('')}
                            </div>
                        </div>
                    `;
                    categoryContainer.appendChild(card);
                });

                periodeSection.appendChild(categoryContainer);
                listContainer.appendChild(periodeSection);
            });

            grandTotalEl.innerText = formatCurrency(totalHutang);
            countEl.innerText = totalItems + " Kategori";
        }

        document.addEventListener('DOMContentLoaded', renderView);

        function openNgalahMobile() {
            const appPackage = "net.ngalah.mobile";
            const playStoreMarketUrl = `market://details?id=${appPackage}`;

            // Metode ini menggunakan ACTION_VIEW yang merupakan standar deep link
            // Kita tambahkan flag khusus agar Android mencoba membuka aplikasi yang sudah ada (bukan tab baru)
            const intentUrl =
                `intent://#Intent;action=android.intent.action.VIEW;package=${appPackage};S.browser_fallback_url=${encodeURIComponent(playStoreMarketUrl)};end`;

            // Eksekusi pemanggilan
            window.location.href = intentUrl;
        }
    </script>
@endpush
