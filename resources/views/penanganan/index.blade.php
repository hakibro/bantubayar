@extends('layouts.dashboard')

@section('title', 'Daftar Siswa')

@push('styles')
    <style>
        /* Custom styles for loading and error modals */
        /* Custom scrollbar agar rapi */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Transisi halus untuk Mobile Filter */
        .filter-overlay {
            transition: opacity 0.3s ease-in-out;
            opacity: 0;
            pointer-events: none;
        }

        .filter-overlay.open {
            opacity: 1;
            pointer-events: auto;
        }

        .filter-drawer {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(100%);
        }

        .filter-drawer.open {
            transform: translateY(0);
        }

        /* Gaya default sudah ada di HTML (text-gray-400, border-gray-200) */

        #resetButton.active {
            background-color: #fef2f2;
            /* red-50 */
            border-color: #fecaca;
            /* red-200 */
            color: #ef4444;
            /* red-500 */
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        #resetButton.active i {
            transform: rotate(-45deg);
            /* Efek sedikit putar agar lebih dinamis */
            transition: transform 0.3s ease;
        }
    </style>
@endpush
@section('content')

    <div class="bg-gray-100 p-6 rounded-xl shadow">
        <form id="filterForm" class="sticky top-0 z-20">
            <div
                class="max-w-7xl mx-auto bg-white border border-blue-100 rounded-2xl shadow-xl shadow-blue-900/5 overflow-hidden">
                <div class="p-4 flex items-center gap-2">
                    <input type="text" name="search" placeholder="Cari nama atau ID Yayasan..."
                        class="flex-grow px-4 py-2.5 bg-slate-50 border border-gray-200 rounded-xl text-sm outline-none focus:border-blue-500 transition-all">

                    <button id="filterButton" type="button" onclick="toggleFilter()"
                        class="md:hidden w-11 h-11 bg-slate-100 rounded-xl"><i class="fas fa-sliders-h"></i></button>
                    <button id="resetButton" type="button"
                        class="h-11 px-4 text-gray-400 border border-gray-200 rounded-xl transition-all"><i
                            class="fas fa-undo"></i></button>
                </div>

                <div id="filterSection" class="hidden md:block bg-slate-50/50 border-t border-gray-100 p-4 md:p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="flex items-center gap-3">
                            <h4 class="text-[10px] font-bold text-blue-600 uppercase w-16 shrink-0">Status</h4>
                            <div
                                class="flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-blue-400 transition-colors">
                                <select name="status"
                                    class="w-full px-2 py-2.5 bg-transparent text-gray-700 text-xs outline-none">
                                    <option value="">Semua</option>
                                    <option value="menunggu_respon">Menunggu Respon</option>
                                    <option value="menunggu_tindak_lanjut">Kesanggupan</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <h4 class="text-[10px] font-bold text-emerald-600 uppercase w-16 shrink-0">Dibuat</h4>
                            <div
                                class="flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-emerald-400 transition-colors">
                                <select name="waktuDibuat"
                                    class="w-full px-2 py-2.5 bg-transparent text-gray-700 text-xs outline-none">
                                    <option value="">Semua Waktu</option>
                                    <option value="0">Hari ini</option>
                                    <option value="1">1 Hari yang lalu</option>
                                    <option value="2">2 Hari yang lalu</option>
                                    <option value="3">3 Hari yang lalu</option>
                                    <option value="4">4 Hari yang lalu</option>
                                    <option value="5">5 Hari yang lalu</option>
                                    <option value="6">6 Hari yang lalu</option>
                                    <option value="7">7 Hari yang lalu</option>
                                    <option value="8">Lebih dari 7 Hari yang lalu</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <h4 class="text-[10px] font-bold text-emerald-600 uppercase w-16 shrink-0">Diperbarui</h4>
                            <div
                                class="flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-emerald-400 transition-colors">
                                <select name="waktuDiperbarui"
                                    class="w-full px-2 py-2.5 bg-transparent text-gray-700 text-xs outline-none">
                                    <option value="">Semua Waktu</option>
                                    <option value="0">Hari ini</option>
                                    <option value="1">1 Hari yang lalu</option>
                                    <option value="2">2 Hari yang lalu</option>
                                    <option value="3">3 Hari yang lalu</option>
                                    <option value="4">4 Hari yang lalu</option>
                                    <option value="5">5 Hari yang lalu</option>
                                    <option value="6">6 Hari yang lalu</option>
                                    <option value="7">7 Hari yang lalu</option>
                                    <option value="8">Lebih dari 7 Hari yang lalu</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <h4 class="text-[10px] font-bold text-red-600 uppercase w-16 shrink-0">Urgent</h4>
                            <div
                                class="flex-1 shadow-sm rounded-xl overflow-hidden border border-gray-200 bg-white focus-within:border-red-400 transition-colors">
                                <select name="terlambat"
                                    class="w-full px-2 py-2.5 bg-transparent text-gray-700 text-xs outline-none">
                                    <option value="">Semua</option>
                                    <option value="7">Terlambat 7 Hari</option>
                                    <option value="14">Terlambat 14 Hari</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="filterOverlay" class="fixed inset-0 bg-black/50 z-10 hidden transition-opacity duration-300"></div>

        {{-- Grid Layout: 1 Kolom di Mobile, 2 di Tablet, 3 di Desktop, 4 di Layar Lebar --}}
        <div id="tableContainer" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mt-6">
            @include('penanganan.partials.list-siswa')
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8">
            {{ $listPenanganan->links() }}
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            function fetchContent() {
                const formData = $('#filterForm').serialize();

                // Efek loading sederhana
                $('#tableContainer').css('opacity', '0.5');

                $.ajax({
                    url: window.location.pathname,
                    data: formData,
                    success: function(response) {
                        $('#tableContainer').html(response);
                        $('#tableContainer').css('opacity', '1');
                        checkFilterActive(); // Update status tombol reset
                    }
                });
            }

            // Trigger saat input atau select berubah
            $('#filterForm input, #filterForm select').on('change input', function() {
                fetchContent();
            });

            // Reset Button AJAX
            $('#resetButton').on('click', function() {
                $('#filterForm')[0].reset();
                $('#filterForm select').val('').trigger('change'); // Memicu fetchContent()
            });
        });

        function checkFilterActive() {
            const isAnyFilled = $('#filterForm input[name="search"]').val() !== "" ||
                $('#filterForm select').filter((i, el) => $(el).val() !== "").length > 0;

            $('#resetButton').toggleClass('active', isAnyFilled);
        }

        // Toggle Filter Mobile (Tetap sama)
        function toggleFilter() {
            const section = document.getElementById('filterSection');
            const btn = document.getElementById('filterButton');
            const overlay = document.getElementById('filterOverlay');
            const icon = btn.querySelector('i');

            if (window.innerWidth < 768) {
                const isHidden = section.classList.toggle('hidden');

                // Toggle Overlay
                overlay.classList.toggle('hidden');

                // Toggle Icon & Warna Tombol
                icon.classList.toggle('fa-sliders-h');
                icon.classList.toggle('fa-times');
                btn.classList.toggle('bg-slate-100');
                btn.classList.toggle('bg-gray-900');
                btn.classList.toggle('text-white');

                // Mencegah scroll pada body saat filter terbuka
                document.body.style.overflow = isHidden ? '' : 'hidden';
            }
        }

        document.getElementById('filterOverlay').addEventListener('click', function() {
            toggleFilter();
        });
    </script>
@endpush
