<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Filter Terpadu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Styling Scrollbar untuk drawer */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        /* Transisi Mobile Filter */
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
    </style>
</head>

<body class="bg-gray-100 font-sans text-gray-700 antialiased min-h-screen p-4 md:p-8">
    <!-- CONTAINER UTAMA (UNIFIED CARD) -->
    <!-- Background putih menyatukan Search dan Filter -->
    <div class="max-w-7xl mx-auto bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <form method="GET" id="filterForm">
            <!-- BAGIAN 1: HEADER (SEARCH & ACTIONS) -->
            <div class="p-4 md:p-6 border-b border-gray-100">
                <div class="flex justify-between gap-4">
                    <!-- Search Input (Mengambil ruang penuh di mobile/kiri di desktop) -->
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" placeholder="Cari nama / ID Person..."
                            class="w-full  pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-sm focus:outline-none focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                            value="{{ request('search') }}" />
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex items-center gap-3 w-auto">
                        <!-- Tombol Filter Mobile (Hanya muncul di layar kecil) -->
                        <button type="button" onclick="toggleFilter()"
                            class="md:hidden flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-50 text-blue-600 border border-blue-200 rounded-lg text-sm font-semibold hover:bg-blue-100 active:scale-95 transition whitespace-nowrap">
                            <i class="fas fa-sliders-h"></i> Filter Data
                        </button>

                        <!-- Tombol Reset (Hanya Desktop) -->
                        <button type="button" onclick="resetForm()"
                            class="hidden md:block px-5 py-2.5 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition font-medium">
                            Reset
                        </button>

                        <!-- Tombol Terapkan (Hanya Desktop) -->
                        <button type="submit"
                            class="hidden md:flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 active:scale-95 transition shadow-md shadow-blue-200">
                            Terapkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- BAGIAN 2: DESKTOP FILTER GRID (Background sedikit berbeda tapi masih satu kartu) -->
            <!-- Background bg-gray-50/30 memberi gradasi halus menyatu dengan putih -->
            <div class="hidden md:block bg-gray-50 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-2 gap-y-6">
                    <!-- Group Formal -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Sekolah Formal
                        </h4>
                        <div class="grid grid-cols-2">
                            <select name="UnitFormal"
                                class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-r-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Lembaga...</option>
                                <!-- Loop Blade -->
                                <option value="SMP">SMP</option>
                                <option value="SMA">SMA</option>
                            </select>
                            <select name="KelasFormal"
                                class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-l-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Kelas...</option>
                                <!-- Loop Blade -->
                                <option value="7">Kelas 7</option>
                                <option value="8">Kelas 8</option>
                            </select>
                        </div>
                    </div>

                    <!-- Group Pondok -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Asrama Pondok
                        </h4>
                        <div class="grid grid-cols-2">
                            <select name="AsramaPondok"
                                class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-r-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Asrama...</option>
                                <option value="Putra 1">Putra 1</option>
                                <option value="Putri 1">Putri 1</option>
                            </select>
                            <select name="KamarPondok"
                                class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-l-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Kamar...</option>
                                <option value="101">101</option>
                                <option value="102">102</option>
                            </select>
                        </div>
                    </div>

                    <!-- Group Diniyah -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                            Diniyah
                        </h4>
                        <div class="grid grid-cols-2">
                            <select name="TingkatDiniyah"
                                class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-r-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Tingkat...</option>
                                <option value="Ula">Ula</option>
                                <option value="Wustho">Wustho</option>
                            </select>
                            <select name="KelasDiniyah"
                                class="w-full px-2 py-2 bg-white border border-gray-200 rounded-lg rounded-l-none text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                <option value="">Kelas D...</option>
                                <option value="1A">1A</option>
                                <option value="1B">1B</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Placeholder Isi Data (Simulasi) -->
            <div class="p-8 text-center text-gray-400 border-t border-gray-100">
                Data tabel akan muncul di sini...
            </div>
        </form>
    </div>

    <!-- MOBILE DRAWER (Tetap Terpisah karena UX Mobile) -->
    <div id="filterOverlay" onclick="toggleFilter()" class="filter-overlay fixed inset-0 bg-black/50 z-40 xl:hidden">
    </div>

    <div id="filterDrawer"
        class="filter-drawer fixed bottom-0 left-0 right-0 bg-white z-50 rounded-t-2xl shadow-2xl xl:hidden flex flex-col max-h-[85vh]">
        <!-- Handle Bar -->
        <div class="flex justify-center pt-3 pb-1">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>

        <!-- Drawer Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">Filter</h3>
            <button onclick="toggleFilter()" class="p-2 text-gray-500 hover:bg-gray-100 rounded-full">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Drawer Content (Scrollable) -->
        <div class="overflow-y-auto p-6 space-y-6 bg-gray-50">
            <!-- Mobile: Group Formal -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider">
                    Sekolah Formal
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <!-- Samakan name dengan desktop agar sinkron -->
                    <select name="UnitFormal"
                        class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Lembaga...</option>
                        <option value="SMP">SMP</option>
                    </select>
                    <select name="KelasFormal"
                        class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Kelas...</option>
                        <option value="7">Kelas 7</option>
                    </select>
                </div>
            </div>

            <!-- Mobile: Group Pondok -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-green-600 uppercase tracking-wider">
                    Asrama Pondok
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <select name="AsramaPondok"
                        class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Asrama...</option>
                        <option value="Putra 1">Putra 1</option>
                    </select>
                    <select name="KamarPondok"
                        class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Kamar...</option>
                        <option value="101">101</option>
                    </select>
                </div>
            </div>

            <!-- Mobile: Group Diniyah -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-amber-600 uppercase tracking-wider">
                    Diniyah
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <select name="TingkatDiniyah"
                        class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Tingkat...</option>
                        <option value="Ula">Ula</option>
                    </select>
                    <select name="KelasDiniyah"
                        class="mobile-select w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="">Kelas...</option>
                        <option value="1A">1A</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Drawer Footer -->
        <div class="p-4 border-t border-gray-200 bg-white grid grid-cols-4 gap-3">
            <button type="button" onclick="resetForm()"
                class="col-span-1 flex items-center justify-center text-gray-500 bg-gray-100 hover:bg-gray-200 rounded-lg transition text-sm py-3">
                <i class="fas fa-undo"></i>
            </button>
            <button type="button" onclick="syncMobileToDesktopAndSubmit()"
                class="col-span-3 flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 active:scale-95 transition shadow-lg shadow-blue-200 font-semibold text-sm">
                Terapkan Filter
            </button>
        </div>
    </div>

    <script>
        function toggleFilter() {
            const drawer = document.getElementById("filterDrawer");
            const overlay = document.getElementById("filterOverlay");
            const body = document.body;
            const isOpen = drawer.classList.contains("open");

            if (isOpen) {
                drawer.classList.remove("open");
                overlay.classList.remove("open");
                body.style.overflow = "";
            } else {
                // Sync values dari Desktop ke Mobile saat dibuka (agar user melihat filter yg sama)
                syncDesktopToMobile();

                drawer.classList.add("open");
                overlay.classList.add("open");
                body.style.overflow = "hidden";
            }
        }

        // Sinkronisasi nilai filter antara Tampilan Desktop dan Mobile Drawer
        function syncDesktopToMobile() {
            const mobileSelects =
                document.querySelectorAll(".mobile-select");
            mobileSelects.forEach((mSelect) => {
                const name = mSelect.name;
                const dSelect = document.querySelector(
                    `#filterForm select[name="${name}"]:not(.mobile-select)`
                );
                if (dSelect) mSelect.value = dSelect.value;
            });
        }

        function syncMobileToDesktopAndSubmit() {
            const mobileSelects =
                document.querySelectorAll(".mobile-select");
            mobileSelects.forEach((mSelect) => {
                const name = mSelect.name;
                const dSelect = document.querySelector(
                    `#filterForm select[name="${name}"]:not(.mobile-select)`
                );
                if (dSelect) dSelect.value = mSelect.value;
            });

            // Tutup drawer lalu submit
            toggleFilter();
            document.getElementById("filterForm").submit();
        }

        function resetForm() {
            const form = document.getElementById("filterForm");
            // Reset semua select di dalam form
            const selects = form.querySelectorAll("select");
            selects.forEach((s) => (s.selectedIndex = 0));
            // Reset text input
            form.querySelector('input[type="text"]').value = "";

            // Submit untuk clear URL
            // form.submit();
        }
    </script>
</body>

</html>
