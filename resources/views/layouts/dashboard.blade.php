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
           bottom-0 left-0 w-full border-t
           md:flex md:flex-col
           flex">

            <!-- BRAND / LOGO (Desktop Only) -->
            <div class="hidden md:flex p-8 items-center gap-3 text-primary text-2xl font-bold">
                <i class="fas fa-credit-card"></i> {{ config('app.name', 'App') }}
            </div>

            <!-- NAVIGATION ITEMS -->
            <div
                class="flex md:flex-col flex-row
               md:space-y-2 md:px-4
               justify-between w-full
               px-6 py-4 pb-6
               text-textMuted text-xs
               md:text-base
               md:mt-4">

                @role('admin')
                    <!-- Beranda -->
                    <a href="{{ route('dashboard') }}"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('admin.dashboard') ? $navActive : $navInactive }}">
                        <i class="fas fa-home text-xl md:w-5"></i>
                        <span>Beranda</span>
                    </a>
                    <!-- Manage Petugas -->
                    <a href="{{ route('admin.petugas.index') }}"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('admin.petugas*') ? $navActive : $navInactive }}">
                        <i class="fas fa-user text-xl md:w-5"></i>
                        <span>Manage Petugas</span>
                    </a>
                    <!-- Siswa -->
                    <a href="{{ route('admin.siswa.index') }}"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('admin.siswa*') ? $navActive : $navInactive }}">
                        <i class="fas fa-list text-xl md:w-5"></i>
                        <span>Siswa</span>
                    </a>
                    <!-- Assign Siswa -->
                    <a href="{{ route('admin.assign.index') }}"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('admin.assign*') ? $navActive : $navInactive }}">
                        <i class="fas fa-check text-xl md:w-5"></i>
                        <span>Assign Siswa</span>
                    </a>
                @endrole

                @role(['petugas', 'bendahara'])
                    <!-- Beranda -->
                    <a href="{{ route('dashboard') }}"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('petugas.dashboard') ? $navActive : $navInactive }}">
                        <i class="fas fa-home text-xl md:w-5"></i>
                        <span>Beranda</span>
                    </a>
                    <!-- Siswa -->
                    <a href="{{ route('petugas.siswa') }}"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('petugas.siswa*') ? $navActive : $navInactive }}">
                        <i class="fas fa-list text-xl md:w-5"></i>
                        <span>Siswa</span>
                    </a>

                    <!-- Penanganan -->
                    <a href="{{ route('penanganan.index') }}"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('penanganan*') ? $navActive : $navInactive }}">
                        <i class="fas fa-credit-card text-xl md:w-5"></i>
                        <span>Penanganan</span>
                    </a>

                    <!-- Akun -->
                    <a href="#"
                        class="flex flex-col md:flex-row items-center gap-1 md:gap-4
                  px-4 py-3 rounded-xl
                  {{ request()->routeIs('akun*') ? $navActive : $navInactive }}">
                        <i class="fas fa-user text-xl md:w-5"></i>
                        <span>Akun</span>
                    </a>
                @endrole
            </div>
        </nav>



        <!-- Main Content -->
        <main class="flex-1 md:ml-64 pb-24 md:pb-2 flex flex-col h-full overflow-hidden relative">
            <!-- Header -->
            <header class="flex justify-between items-center p-4 md:p-6 bg-white shadow-sm z-10">
                <div>
                    @yield('title')
                </div>
                <div class="relative">
                    <button onclick="toggleLogoutPopup()" class="flex items-center gap-3 focus:outline-none">
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ auth()->user()->name }}
                            </p>
                            <p class="text-xs text-gray-500 capitalize">
                                {{ auth()->user()->getRoleNames()->first() ?? 'Petugas' }}
                                {{ auth()->user()->lembaga ?? '-' }}
                            </p>
                        </div>
                    </button>

                    <!-- Popup Logout -->
                    <div id="logoutPopup"
                        class="hidden absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-lg border z-50">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-xl">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

            </header>
            <!-- Content -->
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
            popup.classList.toggle('hidden');
        }

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
