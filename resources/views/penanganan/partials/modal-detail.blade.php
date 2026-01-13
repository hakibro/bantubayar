<!-- 3. Modal Detail Siswa (Big Modal with Payment Data) -->
<div id="modalDetail"
    class="fixed inset-0 bg-black/50 z-50 hidden flex flex-col md:flex-row items-end md:items-center justify-center transition-opacity">
    <div class="bg-white w-full md:w-[700px] md:rounded-3xl rounded-t-3xl p-0 md:p-6 transform translate-y-full transition-transform duration-300 h-[90vh] md:h-auto md:max-h-[90vh] overflow-hidden flex flex-col"
        id="cardDetail">

        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-white md:bg-transparent shrink-0">
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
                remEl.className = 'text-2xl font-bold whitespace-nowrap text-yellow-400';
            } else if (rem > 0) {
                remEl.className = 'text-2xl font-bold whitespace-nowrap text-yellow-300';
            } else {
                remEl.className = 'text-2xl font-bold whitespace-nowrap text-white';
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
                    itemsHtml =
                        '<div class="p-3 text-xs text-gray-400 text-center">Tidak ada item rincian</div>';
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
    </script>
@endpush
