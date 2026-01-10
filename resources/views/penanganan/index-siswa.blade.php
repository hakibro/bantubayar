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
                        <h2 class="text-4xl font-bold text-accent tracking-tight">


                            Rp {{ number_format($totalBelumLunas, 0, ',', '.') }}
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
                    <button onclick="openModal('action')"
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
                    <h3 class="text-lg font-bold">Riwayat Tagihan</h3>
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

    <!-- --- MODALS --- -->

    <!-- 1. Modal Tindak Lanjut (Action) -->
    <div id="modalAction"
        class="fixed inset-0 bg-black/50 z-50 hidden flex items-end md:items-center justify-center transition-opacity">
        <div class="bg-white w-full md:w-[500px] md:rounded-3xl rounded-t-3xl p-6 transform translate-y-full transition-transform duration-300 max-h-[90vh] md:h-auto overflow-y-auto"
            id="cardAction">
            <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-6 md:hidden"></div>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Tindak Lanjut</h3>
                <button onclick="closeModal('action')" class="text-gray-400 hover:text-gray-600"><i
                        class="fas fa-times text-xl"></i></button>
            </div>

            <div class="space-y-6">
                <!-- Jenis Tindakan -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Jenis
                        Tindakan</label>
                    <div class="bg-gray-100 p-1 rounded-xl flex">
                        <button id="btnChat" onclick="toggleActionType('chat')"
                            class="flex-1 py-2 rounded-lg text-sm font-bold bg-white text-primary shadow-sm transition">Chat</button>
                        <button id="btnPhone" onclick="toggleActionType('phone')"
                            class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-white hover:shadow-sm transition">Telepon</button>
                    </div>
                </div>

                <!-- Tombol Hubungi Wali -->
                <button id="btnContactAction" onclick="sendWhatsapp(true)"
                    class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-green-200">
                    <i class="fab fa-whatsapp text-xl"></i> Hubungi Wali
                </button>

                <!-- Catatan -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Catatan</label>
                    <textarea
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none"
                        rows="3" placeholder="Tuliskan hasil komunikasi..."></textarea>
                </div>

                <!-- Kesanggupan (Collapsible) - UPDATED -->
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <button onclick="toggleAccordion('kesanggupan')"
                        class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition text-left">
                        <span class="font-bold text-sm text-gray-700 flex items-center gap-2">
                            <i class="fas fa-file-signature text-primary"></i> Kesanggupan
                        </span>
                        <i id="iconkesanggupan" class="fas fa-chevron-down text-gray-400 text-sm transition-transform"></i>
                    </button>
                    <div id="contentkesanggupan" class="accordion-content bg-white">
                        <div class="p-4 space-y-4 border-t border-gray-100">
                            <div
                                class="bg-blue-50 p-3 rounded-lg border border-blue-100 flex items-start gap-2 text-xs text-blue-800">
                                <i class="fas fa-info-circle mt-0.5"></i>
                                <p>Kirimkan surat pernyataan kesanggupan ke wali murid.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Kesanggupan</label>
                                <input type="date"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-primaryLight outline-none">
                            </div>
                            <button onclick="sendAgreement()"
                                class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl font-bold flex items-center justify-center gap-2 text-sm transition">
                                <i class="fas fa-paper-plane"></i> Kirim Pernyataan Kesanggupan
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Simpan -->
                <button onclick="saveAction()"
                    class="w-full bg-primary text-white py-3 rounded-xl font-bold shadow-md hover:bg-blue-700 transition mt-2">Simpan
                    Catatan</button>
            </div>
        </div>
    </div>

    <!-- 2. Modal Hasil (Result) -->
    <div id="modalResult"
        class="fixed inset-0 bg-black/50 z-50 hidden flex items-end md:items-center justify-center transition-opacity">
        <div class="bg-white w-full md:w-[500px] md:rounded-3xl rounded-t-3xl p-6 transform translate-y-full transition-transform duration-300"
            id="cardResult">
            <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-6 md:hidden"></div>
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Hasil Penanganan</h3>
                <button onclick="closeModal('result')" class="text-gray-400 hover:text-gray-600"><i
                        class="fas fa-times text-xl"></i></button>
            </div>

            <div class="space-y-6">
                <!-- Toggle Hasil -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Hasil</label>
                    <select
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none font-medium">
                        <option>Lunas</option>
                        <option>Isi Saldo</option>
                        <option>Cicilan</option>
                        <option>Tidak Ada Respon</option>
                        <option>HP Tidak Aktif</option>
                    </select>
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Catatan
                        Hasil</label>
                    <textarea
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none"
                        rows="3" placeholder="Detail hasil..."></textarea>
                </div>

                <!-- Rating Wali -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Rating Wali
                        Murid</label>
                    <div class="flex gap-2 text-2xl text-gray-200 star-rating justify-center py-2" id="starContainer">
                        <i class="fas fa-star" onclick="rate(1)"></i>
                        <i class="fas fa-star" onclick="rate(2)"></i>
                        <i class="fas fa-star" onclick="rate(3)"></i>
                        <i class="fas fa-star" onclick="rate(4)"></i>
                        <i class="fas fa-star" onclick="rate(5)"></i>
                    </div>
                </div>

                <!-- Simpan -->
                <button onclick="saveResult()"
                    class="w-full bg-primary text-white py-3 rounded-xl font-bold shadow-md hover:bg-blue-700 transition mt-2">Simpan
                    Status</button>
            </div>
        </div>
    </div>

    <!-- 3. Modal Detail Siswa (Big Modal with Payment Data) -->
    <div id="modalDetail"
        class="fixed inset-0 bg-black/50 z-50 hidden flex flex-col md:flex-row items-end md:items-center justify-center transition-opacity">
        <div class="bg-white w-full md:w-[700px] md:rounded-3xl rounded-t-3xl p-0 md:p-6 transform translate-y-full transition-transform duration-300 h-[90vh] md:h-auto md:max-h-[90vh] overflow-hidden flex flex-col"
            id="cardDetail">

            <div
                class="p-5 border-b border-gray-100 flex justify-between items-center bg-white md:bg-transparent shrink-0">
                <h3 class="text-xl font-bold">Info Siswa & Pembayaran</h3>
                <button onclick="closeModal('detail')"
                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center hover:bg-gray-200 transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <div class="flex border-b border-gray-100 px-6 pt-2 shrink-0">
                <button onclick="switchTab('info')" id="tabInfo"
                    class="pb-3 px-4 text-sm font-bold border-b-2 border-primary text-primary transition">Info
                    Siswa</button>
                <button onclick="switchTab('payment')" id="tabPayment"
                    class="pb-3 px-4 text-sm font-bold border-b-2 border-transparent text-textMuted hover:text-gray-600 transition">Pembayaran</button>
            </div>

            <div class="p-6 overflow-y-auto flex-1">
                <div id="contentInfo" class="space-y-6">
                    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">

                        <!-- Header -->
                        <div class="flex items-start justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">
                                    {{ $siswa->nama }}
                                </h2>
                                <p class="text-sm text-textMuted mt-1">
                                    ID: {{ $siswa->idperson }} • {{ $siswa->phone ?? 'Tidak ada No. HP' }}
                                </p>
                            </div>

                            <!-- Badge Status (opsional statis dulu) -->
                            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700">
                                Aktif
                            </span>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-100 my-4"></div>

                        <!-- Informasi Pendidikan -->
                        <div class="space-y-3 text-sm">

                            <!-- Unit Formal -->
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-primary flex items-center justify-center">
                                    <i class="fas fa-school text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">Pendidikan Formal</p>
                                    <p class="text-textMuted">
                                        {{ $siswa->UnitFormal ?? '-' }} • Kelas {{ $siswa->KelasFormal ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Asrama / Pondok -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                                    <i class="fas fa-bed text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">Asrama / Pondok</p>
                                    <p class="text-textMuted">
                                        {{ $siswa->AsramaPondok ?? '-' }} • Kamar {{ $siswa->KamarPondok ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Diniyah -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                                    <i class="fas fa-mosque text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800">Pendidikan Diniyah</p>
                                    <p class="text-textMuted">
                                        {{ $siswa->TingkatDiniyah ?? '-' }} • Kelas {{ $siswa->KelasDiniyah ?? '-' }}
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <div id="contentPayment" class="hidden space-y-6">
                    <div class="flex gap-2 overflow-x-auto no-scrollbar py-2" id="periodTabsContainer">
                        <!-- Period Tabs -->
                    </div>
                    <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-2xl p-5 text-white shadow-lg">
                        <div class="flex flex-col md:flex-row justify-between items-center md:items-start gap-6 md:gap-8">

                            <!-- Bagian Kiri: Sisa (Highlight Utama) -->
                            <div class="flex flex-col items-center md:items-start w-full md:w-auto">
                                <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider mb-1">Sisa</p>
                                <h3 id="summaryRemaining"
                                    class="text-4xl md:text-xl font-extrabold leading-tight whitespace-nowrap">Rp 0
                                </h3>
                            </div>

                            <!-- Bagian Kanan: Total Tagihan & Total Bayar -->
                            <div class="flex flex-row justify-between w-full md:justify-end md:gap-12">

                                <div class="flex flex-col text-center md:text-left">
                                    <p class="text-blue-200 text-xs font-medium mb-1">Total Tagihan</p>
                                    <h3 id="summaryTotalPaid" class="text-xl font-bold">Rp 0</h3>
                                </div>

                                <div class="flex flex-col text-center md:text-left">
                                    <p class="text-blue-200 text-xs font-medium mb-1">Total Bayar</p>
                                    <h3 id="summaryTotalBilled" class="text-xl font-bold">Rp 0</h3>
                                </div>

                            </div>
                        </div>

                    </div>
                    <div id="categoriesList" class="space-y-3"></div>
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

            // --- Tindak Lanjut Logic ---
            let currentActionType = 'chat';

            function toggleActionType(type) {
                currentActionType = type;
                const btnChat = document.getElementById('btnChat');
                const btnPhone = document.getElementById('btnPhone');
                const btnContact = document.getElementById('btnContactAction');

                if (type === 'chat') {
                    btnChat.className = "flex-1 py-2 rounded-lg text-sm font-bold bg-white text-primary shadow-sm transition";
                    btnPhone.className =
                        "flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-white hover:shadow-sm transition";
                    btnContact.innerHTML = '<i class="fab fa-whatsapp text-xl"></i> Hubungi Wali';
                    btnContact.className =
                        "w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-green-200";
                } else {
                    btnPhone.className = "flex-1 py-2 rounded-lg text-sm font-bold bg-white text-primary shadow-sm transition";
                    btnChat.className =
                        "flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-white hover:shadow-sm transition";
                    btnContact.innerHTML = '<i class="fas fa-phone-alt text-xl"></i> Hubungi Wali';
                    btnContact.className =
                        "w-full bg-gray-800 hover:bg-gray-900 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-gray-300";
                }
            }



            function sendWhatsapp(isWithDebt) {
                const phone = "6281234567890";
                const text = isWithDebt ? "Halo Bapak/Ibu Wali, kami menginformasikan tagihan Alexandre Santoso." :
                    "Halo Bapak/Ibu Wali, kami ingin konfirmasi.";
                window.open(`https://wa.me/${phone}?text=${encodeURIComponent(text)}`, '_blank');
            }

            function saveAction() {
                closeModal('action');
                showToast('Catatan tindakan disimpan');
            }

            function sendAgreement() {
                showToast('Pernyataan kesanggupan dikirim');
            }

            // --- Hasil Logic ---
            let currentRating = 0;

            function rate(n) {
                currentRating = n;
                const stars = document.getElementById('starContainer').children;
                for (let i = 0; i < 5; i++) {
                    if (i < n) stars[i].classList.add('active');
                    else stars[i].classList.remove('active');
                }
            }

            function saveResult() {
                closeModal('result');
                showToast('Status penanganan disimpan');
            }

            // 1. Inject Data Real dari PHP ke Javascript
            // Mengambil array pembayaran dari data JSON asli
            const paymentData = @json($siswa->pembayaran ?? []);

            let activePeriodIndex = 0;

            // --- Payment Tab Logic (Disesuaikan untuk Data Asli) ---
            function renderPaymentTab() {
                const tabsContainer = document.getElementById('periodTabsContainer');
                const categoriesList = document.getElementById('categoriesList');

                // Cek jika data kosong
                if (!paymentData || paymentData.length === 0) {
                    tabsContainer.innerHTML = '';
                    categoriesList.innerHTML =
                        '<div class="text-center p-6 text-gray-500 bg-white rounded-xl">Tidak ada data pembayaran.</div>';
                    document.getElementById('summaryTotalBilled').innerText = 'Rp 0';
                    document.getElementById('summaryTotalPaid').innerText = 'Rp 0';
                    document.getElementById('summaryRemaining').innerText = 'Rp 0';
                    return;
                }

                // Ambil data periode yang aktif
                const period = paymentData[activePeriodIndex];

                // --- RENDER TABS ---
                tabsContainer.innerHTML = '';
                paymentData.forEach((data, index) => {
                    const btn = document.createElement('button');
                    // PENTING: Mengambil 'periode' dari level utama object
                    const periodName = data.periode;
                    const isActive = index === activePeriodIndex;

                    btn.className =
                        `shrink-0 px-4 py-2 rounded-full text-xs font-bold border transition duration-200 ${isActive ? 'bg-primary text-white border-primary' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'}`;
                    btn.innerText = periodName;
                    btn.onclick = () => {
                        activePeriodIndex = index;
                        renderPaymentTab();
                    };
                    tabsContainer.appendChild(btn);
                });

                // --- RENDER SUMMARY ---
                // PENTING: Mengakses summary melalui path 'data.summary'
                const summaryData = period.data ? period.data.summary : {};

                document.getElementById('summaryTotalBilled').innerText = formatCurrency(summaryData.total_billed || 0);
                document.getElementById('summaryTotalPaid').innerText = formatCurrency(summaryData.total_paid || 0);

                const rem = summaryData.total_remaining || 0;
                const remEl = document.getElementById('summaryRemaining');
                remEl.innerText = formatCurrency(rem);

                // Logika Warna Sisa:
                // Minus (Overpaid/Kurang Bayar) -> Hijau
                // Nol (Lunas) -> Putih
                // Plus (Hutang) -> Kuning/Merah
                if (rem < 0) {
                    remEl.className = 'text-2xl font-bold text-yellow-400';
                } else if (rem > 0) {
                    remEl.className = 'text-2xl font-bold text-yellow-300';
                } else {
                    remEl.className = 'text-2xl font-bold text-white';
                }

                // --- RENDER KATEGORI ---
                categoriesList.innerHTML = '';
                // PENTING: Mengakses categories melalui path 'data.categories'
                const categories = period.data ? period.data.categories : [];

                if (categories.length === 0) {
                    categoriesList.innerHTML =
                        '<div class="text-center p-4 text-gray-400 text-sm bg-white rounded-xl">Tidak ada kategori periode ini.</div>';
                }

                categories.forEach((cat, catIndex) => {
                    // Logika Icon berdasarkan sisa pembayaran kategori
                    const isFullyPaid = cat.summary.fully_paid;
                    const iconColor = isFullyPaid ? 'text-green-500 bg-green-50' : 'text-red-500 bg-red-50';
                    const iconClass = isFullyPaid ? 'fa-check-circle' : 'fa-exclamation-circle';

                    const catCard = document.createElement('div');
                    catCard.className = 'bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm';

                    // Header Accordion
                    const header = document.createElement('div');
                    header.className =
                        'flex justify-between items-center p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition';
                    header.onclick = () => toggleAccordion(`catBody${catIndex}`);

                    header.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full ${iconColor} flex items-center justify-center text-sm">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-800">${cat.category_name}</h4>
                        <p class="text-xs text-gray-500">Tagih: ${formatCurrency(cat.summary.total_paid)} </p>
                        <p class="text-xs text-gray-500">Bayar: ${formatCurrency(cat.summary.total_billed)}</p>
                    </div>
                </div>
                <div class="text-right flex items-center gap-4">
                    <div class='flex flex-col'><p class="font-bold text-sm ${cat.summary.fully_paid === false ? 'text-red-500' : 'text-gray-800'}">
                        ${formatCurrency(cat.summary.total_remaining)}
                    </p>
                    <p class="text-[10px] text-gray-500">Sisa</p></div>
                        <i id="iconcatBody${catIndex}" class="fas fa-chevron-down text-gray-400 text-sm transition-transform"></i>

                </div>

            `;

                    // Body Isi (Items)
                    const body = document.createElement('div');
                    body.id = `contentcatBody${catIndex}`;
                    body.className = 'accordion-content bg-white divide-y divide-gray-100'; // Hidden by default

                    let itemsHtml = '';
                    if (cat.items && cat.items.length > 0) {
                        cat.items.forEach(item => {
                            const statusColor = item.remaining_balance === 0 ? 'text-green-600' : (item
                                .remaining_balance < 0 ? 'text-red-500' : 'text-gray-800');
                            itemsHtml += `
                        <div class="p-3 flex justify-between items-center text-xs hover:bg-gray-50">
                            <div>
                                <span class="font-medium text-gray-700 block">${item.unit_name}</span>
                                <div class="text-[10px] text-gray-400 mt-1">
                                    Tagih: ${formatCurrency(item.amount_paid)}  &bull; Bayar:  ${formatCurrency(item.amount_billed)}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold ${statusColor}">${formatCurrency(item.remaining_balance)}</span>
                            </div>
                        </div>`;
                        });
                    } else {
                        itemsHtml = '<div class="p-3 text-xs text-gray-400 text-center">Tidak ada item rincian</div>';
                    }

                    body.innerHTML = itemsHtml;

                    catCard.appendChild(header);
                    catCard.appendChild(body);
                    categoriesList.appendChild(catCard);
                });

            }



            function switchTab(tabName) {
                const tabInfo = document.getElementById('tabInfo');
                const tabPayment = document.getElementById('tabPayment');
                const contentInfo = document.getElementById('contentInfo');
                const contentPayment = document.getElementById('contentPayment');
                if (tabName === 'info') {
                    tabInfo.className = "pb-3 px-4 text-sm font-bold border-b-2 border-primary text-primary transition";
                    tabPayment.className =
                        "pb-3 px-4 text-sm font-bold border-b-2 border-transparent text-textMuted hover:text-gray-600 transition";
                    contentInfo.classList.remove('hidden');
                    contentPayment.classList.add('hidden');
                } else {
                    tabPayment.className = "pb-3 px-4 text-sm font-bold border-b-2 border-primary text-primary transition";
                    tabInfo.className =
                        "pb-3 px-4 text-sm font-bold border-b-2 border-transparent text-textMuted hover:text-gray-600 transition";
                    contentPayment.classList.remove('hidden');
                    contentInfo.classList.add('hidden');
                    renderPaymentTab();
                }
            }

            function toggleAccordion(id) {
                console.log(id);
                const content = document.getElementById(`content${id}`);
                const icon = document.getElementById(`icon${id}`);
                console.log(content, icon);
                content.classList.toggle('active');
                icon.classList.toggle('rotate-180');
            }
        </script>
    @endpush
@endsection
