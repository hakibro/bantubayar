<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo-fav.png') }}">

    <title>{{ config('app.name', 'Dashboard') }}</title>

    @vite(['resources/js/app.js'])


    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        @media (max-width: 640px) {
            .pagination-wrapper svg {
                display: none;
            }
        }


        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Accordion Animation */
        .accordion-content {
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }

        .accordion-content.active {
            max-height: 500px;
            opacity: 1;
        }

        /* Star Rating */
        .star-rating i {
            cursor: pointer;
            transition: color 0.2s;
        }

        .star-rating i.active {
            color: #F59E0B;
            /* Amber */
        }
    </style>
    @stack('styles')
</head>

<body class="bg-bgBody text-gray-800 font-sans overflow-x-hidden">

    <div class="flex h-screen">
        @php
            $navActive = 'bg-primaryLight text-primary font-semibold';
            $navInactive = 'hover:bg-gray-50 text-gray-500 hover:text-primary transition';
        @endphp

        <nav
            class="fixed z-40 bg-white border-gray-100 
            md:inset-y-0 md:left-0 md:w-64 md:border-r 
            bottom-0 w-full border-t 
            md:flex md:flex-col justify-between shadow-2xl md:shadow-none">

            <div class="w-full">
                <div class="hidden md:flex p-8 items-center gap-3 text-primary text-2xl font-bold italic">
                    <i class="fas fa-credit-card"></i> {{ config('app.name', 'App') }}
                </div>

                <div
                    class="flex md:flex-col flex-row 
                    md:space-y-2 md:px-4 
                    justify-around md:justify-start w-full 
                    px-2 py-3 md:py-4
                    text-gray-500 text-[10px] md:text-base">

                    @php
                        // Logika Role-Based Menu untuk mempersingkat kode
                        $isAdmin = auth()->user()->hasRole('admin');
                        $menus = $isAdmin
                            ? [
                                [
                                    'route' => 'dashboard',
                                    'icon' => 'fa-home',
                                    'label' => 'Beranda',
                                    'active' => 'dashboard',
                                ],
                                [
                                    'route' => 'admin.petugas.index',
                                    'icon' => 'fa-user-shield',
                                    'label' => 'Petugas',
                                    'active' => 'admin.petugas*',
                                ],
                                [
                                    'route' => 'admin.siswa.index',
                                    'icon' => 'fa-user-graduate',
                                    'label' => 'Siswa',
                                    'active' => 'admin.siswa*',
                                ],
                                [
                                    'route' => 'admin.assign.index',
                                    'icon' => 'fa-clipboard-check',
                                    'label' => 'Assign',
                                    'active' => 'admin.assign*',
                                ],
                            ]
                            : [
                                [
                                    'route' => 'dashboard',
                                    'icon' => 'fa-home',
                                    'label' => 'Beranda',
                                    'active' => 'petugas.dashboard',
                                ],
                                [
                                    'route' => 'petugas.siswa',
                                    'icon' => 'fa-users',
                                    'label' => 'Siswa',
                                    'active' => 'petugas.siswa*',
                                ],
                                [
                                    'route' => 'penanganan.index',
                                    'icon' => 'fa-hand-holding-usd',
                                    'label' => 'Proses',
                                    'active' => 'penanganan*',
                                ],
                            ];
                    @endphp

                    @foreach ($menus as $menu)
                        <a href="{{ route($menu['route']) }}"
                            class="flex flex-col md:flex-row items-center gap-1 md:gap-4 
                          px-3 py-2 md:px-4 md:py-3 rounded-xl transition-all duration-200
                          {{ request()->routeIs($menu['active']) ? $navActive : $navInactive }}">
                            <i class="fas {{ $menu['icon'] }} text-lg md:text-xl md:w-6 text-center"></i>
                            <span class="font-medium">{{ $menu['label'] }}</span>
                        </a>
                    @endforeach

                    <button onclick="toggleLogoutPopup()"
                        class="flex md:hidden flex-col items-center gap-1 px-3 py-2 text-gray-500">
                        <i class="fas fa-user-circle text-lg"></i>
                        <span class="font-medium">Akun</span>
                    </button>
                </div>
            </div>

            <div class="px-4 py-0 md:py-4 border-t border-gray-50">
                <div class="relative">
                    <button onclick="toggleLogoutPopup()"
                        class="hidden md:flex w-full items-center gap-3 p-3 hover:bg-gray-50 rounded-2xl transition group">
                        <div
                            class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="text-left overflow-hidden">
                            <p class="text-sm font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">
                                {{ auth()->user()->getRoleNames()->first() }}
                            </p>
                        </div>
                        <i class="fas fa-ellipsis-v ml-auto text-gray-300 group-hover:text-gray-500"></i>
                    </button>

                    <div id="logoutPopup"
                        class="hidden absolute bottom-full left-0 mb-2 w-full bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                        <div class="p-5">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold shadow-md">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-sm font-bold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">
                                        {{ auth()->user()->getRoleNames()->first() }} •
                                        {{ auth()->user()->lembaga ?? 'Umum' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="py-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full text-left px-5 py-4 text-sm text-red-600 font-bold hover:text-red-800 hover:cursor-pointer flex items-center gap-3 transition">
                                    <i class="fas fa-sign-out-alt"></i> Logout Aplikasi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>



            <div id="mobileOverlay" onclick="toggleLogoutPopup()"
                class="hidden fixed inset-0 bg-black/20 backdrop-blur-sm z-40 md:hidden"></div>

        </nav>



        <!-- Main Content -->
        <main class="flex-1 md:ml-64 pb-24 md:pb-2 flex flex-col h-full overflow-hidden relative">

            <div class="max-w-full overflow-auto bg-bgBody">
                @yield('content')
            </div>
        </main>
    </div>


    <!-- Toast Notification -->
    <div id="toast"
        class="fixed top-5 left-1/2 transform -translate-x-1/2 
           bg-gray-800 text-white px-6 py-3 rounded-full shadow-lg 
           z-[100] transition-all duration-300 
           opacity-0 translate-y-[-20px] pointer-events-none 
           flex items-center gap-3">

        <i id="toastIcon" class="fas fa-check-circle text-green-400"></i>
        <span id="toastMsg" class="text-sm font-medium">Berhasil</span>
    </div>

    <!-- Loading Modal -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg flex items-center">
            <i class="fas fa-spinner fa-spin text-3xl text-blue-600 mr-3"></i>
            <span class="text-lg font-semibold">Sedang memperbarui data pembayaran...</span>
        </div>
    </div>
    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
            <i class="fas fa-times-circle text-red-600 text-4xl mb-2"></i>
            <h2 class="text-xl font-semibold">Gagal!</h2>
            <p id="errorMessage" class="mt-2"></p>
            <button onclick="closeError()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded">Tutup</button>
        </div>
    </div>

    <!-- Script -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    @stack('scripts')
    <script>
        function toggleLogoutPopup() {
            const popup = document.getElementById('logoutPopup');
            const overlay = document.getElementById('mobileOverlay');

            if (popup.classList.contains('hidden')) {
                // Tampilkan
                popup.classList.remove('hidden');
                if (overlay) overlay.classList.remove('hidden');

                // Atur Class Berdasarkan Lebar Layar
                if (window.innerWidth < 768) {
                    // Tampilan Mobile: Melayang di tengah bawah
                    popup.className =
                        "fixed bottom-24 left-6 right-6 bg-white rounded-[2rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] border-none z-50 animate-bounce-in";
                } else {
                    // Tampilan Desktop: Di atas profile sidebar
                    popup.className =
                        "absolute bottom-full left-0 mb-2 w-full bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50";
                }
            } else {
                // Sembunyikan
                popup.classList.add('hidden');
                if (overlay) overlay.classList.add('hidden');
            }
        }
        // Menutup popup jika klik di luar area
        document.addEventListener('click', function(event) {
            const nav = document.querySelector('nav');
            const popup = document.getElementById('logoutPopup');
            const overlay = document.getElementById('mobileOverlay');

            if (!nav.contains(event.target)) {
                popup.classList.add('hidden');
                if (overlay) overlay.classList.add('hidden');
            }
        });

        function showToast(msg, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMsg');
            const toastIcon = document.getElementById('toastIcon');

            toastMsg.innerText = msg;

            // reset icon & color
            toastIcon.className = 'fas';

            switch (type) {
                case 'error':
                    toastIcon.classList.add('fa-times-circle', 'text-red-400');
                    break;
                case 'warning':
                    toastIcon.classList.add('fa-exclamation-circle', 'text-yellow-400');
                    break;
                default:
                    toastIcon.classList.add('fa-check-circle', 'text-green-400');
            }

            toast.classList.remove(
                'opacity-0',
                'translate-y-[-20px]',
                'pointer-events-none'
            );

            setTimeout(() => {
                toast.classList.add(
                    'opacity-0',
                    'translate-y-[-20px]',
                    'pointer-events-none'
                );
            }, 3600);
        }


        function closeError() {
            document.getElementById("errorModal").classList.add("hidden");
        }

        function syncPembayaran(id) {
            console.log(id);

            document.getElementById("loadingModal").classList.remove("hidden");

            fetch("{{ url('petugas/siswa/sync-pembayaran-siswa') }}/" + id, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(res => res.json())
                .then(data => {

                    if (!data.status) throw new Error(data.message);
                })
                .catch(err => {
                    document.getElementById("errorMessage").innerText = err.message || 'Gagal sync';
                    document.getElementById("errorModal").classList.remove("hidden");
                })
                .finally(() => {
                    document.getElementById("loadingModal").classList.add("hidden");
                    location.reload();

                });

        }
    </script>

</body>

</html>
