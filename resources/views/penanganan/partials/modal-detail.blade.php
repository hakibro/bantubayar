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
