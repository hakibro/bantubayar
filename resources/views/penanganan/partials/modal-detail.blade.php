<!-- Modal Detail Siswa (Info Siswa + Pembayaran) -->
<div id="modalDetail"
    class="fixed inset-0 bg-black/50 z-50 hidden flex flex-col md:flex-row items-end md:items-center justify-center transition-opacity">
    <div class="bg-white w-full md:w-[700px] md:rounded-3xl rounded-t-3xl p-0 md:p-6 transform translate-y-full transition-transform duration-300 h-[90vh] md:h-auto md:max-h-[90vh] overflow-hidden flex flex-col"
        id="cardDetail">

        <!-- Header Modal -->
        <div class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col gap-4">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 leading-tight">{{ $siswa->nama }}</h3>
                    <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                        <span class="bg-gray-100 px-1.5 py-0.5 rounded font-mono">ID: {{ $siswa->idperson }}</span>
                        <span>•</span>
                        <i class="fas fa-phone-alt text-green-500"></i>
                        {{ $siswa->phone ?? '-' }}
                    </div>
                </div>
                <button onclick="closeModal('detail')"
                    class="p-2 rounded-full bg-gray-50 hover:bg-gray-100 text-gray-400 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 p-2 rounded-lg bg-blue-50/50">
                    <i class="fas fa-school text-blue-600 w-5 text-center"></i>
                    <div>
                        <p class="text-[10px] uppercase text-blue-500 font-bold leading-none mb-1">Formal</p>
                        <p class="text-sm text-gray-700 font-medium">{{ $siswa->UnitFormal ?? '-' }} •
                            {{ $siswa->KelasFormal ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-2 rounded-lg bg-orange-50/50">
                    <i class="fas fa-bed text-orange-600 w-5 text-center"></i>
                    <div>
                        <p class="text-[10px] uppercase text-orange-500 font-bold leading-none mb-1">Asrama</p>
                        <p class="text-sm text-gray-700 font-medium">{{ $siswa->AsramaPondok ?? '-' }} •
                            {{ $siswa->KamarPondok ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-2 rounded-lg bg-emerald-50/50">
                    <i class="fas fa-mosque text-emerald-600 w-5 text-center"></i>
                    <div>
                        <p class="text-[10px] uppercase text-emerald-500 font-bold leading-none mb-1">Diniyah</p>
                        <p class="text-sm text-gray-700 font-medium">{{ $siswa->TingkatDiniyah ?? '-' }} •
                            {{ $siswa->KelasDiniyah ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-6 overflow-y-auto flex-1 space-y-6">


            <!-- ========== BAGIAN PEMBAYARAN ========== -->
            <div class="space-y-6">
                <div class="flex gap-2 overflow-x-auto no-scrollbar py-2" id="periodTabsContainer"></div>
                <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-2xl p-5 text-white shadow-lg">
                    <div class="flex justify-between items-center md:items-start gap-6 md:gap-8">

                        <div class="flex flex-col md:flex-row justify-between w-full md:justify-start md:gap-10">
                            <div class="flex flex-col text-center md:text-left">
                                <p class="text-blue-200 text-xs font-medium mb-1">Total Tagihan</p>
                                <h3 id="summaryTotalPaid" class="text-xl font-bold">Rp 0</h3>
                            </div>
                            <div class="flex flex-col text-center md:text-left">
                                <p class="text-blue-200 text-xs font-medium mb-1">Total Bayar</p>
                                <h3 id="summaryTotalBilled" class="text-xl font-bold">Rp 0</h3>
                            </div>
                        </div>
                        <div class="flex flex-col items-center md:items-start w-full md:w-auto">
                            <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider mb-1">Sisa</p>
                            <h3 id="summaryRemaining"
                                class="text-4xl md:text-xl font-extrabold leading-tight whitespace-nowrap">Rp 0</h3>

                        </div>
                    </div>
                    <p id="kelasInfo"
                        class="text-white text-xs font-semibold uppercase mt-2 text-center border-t border-blue-400 pt-2">
                    </p>
                </div>
                <div id="categoriesList" class="space-y-3"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Data pembayaran dari PHP (collection of SiswaPembayaran)
        const paymentData = @json($siswa->pembayaran ?? []);
        let activePeriodIndex = 0;

        function formatCurrency(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(value).replace(/\s/g, '');
        }

        // Fungsi toggle accordion menggunakan class 'active' (CSS dari dashboard)
        function togglePaymentAccordion(bodyId) {
            const body = document.getElementById(`content${bodyId}`);
            const icon = document.getElementById(`icon${bodyId}`);
            if (!body) return;

            // Toggle class 'active' untuk mengatur max-height dan opacity
            body.classList.toggle('active');

            // Putar icon
            if (icon) {
                if (body.classList.contains('active')) {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        }

        function renderPaymentTab() {
            const tabsContainer = document.getElementById('periodTabsContainer');
            const categoriesList = document.getElementById('categoriesList');

            if (!paymentData || paymentData.length === 0) {
                tabsContainer.innerHTML = '';
                categoriesList.innerHTML =
                    '<div class="text-center p-6 text-gray-500 bg-white rounded-xl">Tidak ada data pembayaran.</div>';
                document.getElementById('summaryTotalBilled').innerText = 'Rp 0';
                document.getElementById('summaryTotalPaid').innerText = 'Rp 0';
                document.getElementById('summaryRemaining').innerText = 'Rp 0';
                document.getElementById('kelasInfo').innerText = '-';
                return;
            }

            const period = paymentData[activePeriodIndex];
            const summaryData = period.summary || {};
            const categories = period.data || [];

            // Render tabs periode
            tabsContainer.innerHTML = '';
            paymentData.forEach((data, index) => {
                const btn = document.createElement('button');
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

            // Render summary periode
            document.getElementById('summaryTotalBilled').innerText = formatCurrency(summaryData.total_billed || 0);
            document.getElementById('summaryTotalPaid').innerText = formatCurrency(summaryData.total_paid || 0);
            document.getElementById('kelasInfo').innerText = period.kelas_info || '-';
            const remaining = summaryData.total_remaining || 0;
            const remEl = document.getElementById('summaryRemaining');
            remEl.innerText = formatCurrency(remaining);
            if (remaining < 0) {
                remEl.className = 'text-2xl font-bold whitespace-nowrap text-yellow-400';
            } else if (remaining > 0) {
                remEl.className = 'text-2xl font-bold whitespace-nowrap text-yellow-300';
            } else {
                remEl.className = 'text-2xl font-bold whitespace-nowrap text-white';
            }

            // Render kategori
            categoriesList.innerHTML = '';
            if (categories.length === 0) {
                categoriesList.innerHTML =
                    '<div class="text-center p-4 text-gray-400 text-sm bg-white rounded-xl">Tidak ada kategori untuk periode ini.</div>';
                return;
            }

            categories.forEach((cat, idx) => {
                const isFullyPaid = cat.summary?.fully_paid ?? true;
                const iconColor = isFullyPaid ? 'text-green-500 bg-green-50' : 'text-red-500 bg-red-50';
                const iconClass = isFullyPaid ? 'fa-check-circle' : 'fa-exclamation-circle';

                const catCard = document.createElement('div');
                catCard.className = 'bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm';

                const header = document.createElement('div');
                header.className =
                    'flex justify-between items-center p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 transition';
                header.onclick = () => togglePaymentAccordion(`catBody${idx}`);

                header.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full ${iconColor} flex items-center justify-center text-sm">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-gray-800">${escapeHtml(cat.category_name)}</h4>
                        <p class="text-xs text-gray-500">Tagih: ${formatCurrency(cat.summary.total_billed)}</p>
                        <p class="text-xs text-gray-500">Bayar: ${formatCurrency(cat.summary.total_paid)}</p>
                    </div>
                </div>
                <div class="text-right flex items-center gap-4">
                    <div class='flex flex-col'>
                        <p class="font-bold text-sm ${!isFullyPaid ? 'text-red-500' : 'text-gray-800'}">
                            ${formatCurrency(cat.summary.total_remaining)}
                        </p>
                        <p class="text-[10px] text-gray-500">Sisa</p>
                    </div>
                    <i id="iconcatBody${idx}" class="fas fa-chevron-down text-gray-400 text-sm transition-transform"></i>
                </div>
            `;

                const body = document.createElement('div');
                body.id = `contentcatBody${idx}`;
                // Hanya class accordion-content (tanpa 'hidden') - CSS dari dashboard akan menangani animasi
                body.className = 'accordion-content bg-white divide-y divide-gray-100';

                let itemsHtml = '';
                if (cat.items && cat.items.length > 0) {
                    cat.items.forEach(item => {
                        const statusColor = item.remaining_balance === 0 ? 'text-green-600' : (item
                            .remaining_balance < 0 ? 'text-red-500' : 'text-gray-800');
                        itemsHtml += `
                        <div class="p-3 flex justify-between items-center text-xs hover:bg-gray-50">
                            <div>
                                <span class="font-medium text-gray-700 block">${escapeHtml(item.unit_name)}</span>
                                <div class="text-[10px] text-gray-400 mt-1">
                                    Tagih: ${formatCurrency(item.amount_paid)} &bull; Bayar: ${formatCurrency(item.amount_billed)}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold ${statusColor}">${formatCurrency(item.remaining_balance)}</span>
                            </div>
                        </div>
                    `;
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

        // Helper untuk menghindari XSS
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if (m === '&') return '&amp;';
                if (m === '<') return '&lt;';
                if (m === '>') return '&gt;';
                return m;
            });
        }

        // Panggil renderPaymentTab saat modal dibuka
        document.addEventListener('modalOpened', function(e) {
            if (e.detail && e.detail.modalId === 'modalDetail') {
                renderPaymentTab();
            }
        });
    </script>
@endpush
