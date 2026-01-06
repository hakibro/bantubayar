<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Dashboard') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

</head>

<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md hidden md:block">
            <div class="p-4 border-b">
                <h1 class="text-2xl font-bold text-indigo-600">{{ config('app.name', 'App') }}</h1>
            </div>
            <nav class="mt-4 space-y-1">

                @role('admin')
                    <a href="{{ route('admin.dashboard') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.petugas.index') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-user-tie mr-2"></i> Manage Petugas
                    </a>
                    <a href="{{ route('admin.siswa.index') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-user-graduate mr-2"></i> Manage Siswa
                    </a>
                    <a href="{{ route('admin.assign.index') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-check mr-2"></i> Assign Siswa
                    </a>
                    <a href="#" class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-cog mr-2"></i> Autosend WA
                    </a>
                @endrole

                @role('bendahara')
                    <a href="{{ route('bendahara.dashboard') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="{{ route('bendahara.penanganan.index') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-users mr-2"></i> Data Siswa
                    </a>
                    <a href="{{ route('penanganan.index') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-users mr-2"></i> Data Penanganan
                    </a>
                @endrole
                @role('petugas')
                    <a href="{{ route('petugas.dashboard') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-home mr-2"></i> Dashboard
                    </a>
                    <a href="{{ route('petugas.penanganan.index') }}"
                        class="block px-6 py-2 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600">
                        <i class="fas fa-users mr-2"></i> Data Penanganan
                    </a>
                @endrole

            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Bar -->
            <header class="bg-white shadow px-6 py-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                <div class="flex items-center space-x-3">
                    <span class="text-gray-600">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">Logout</button>
                    </form>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">

                {{-- ERROR --}}
                @if (session('error'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center">
                        <div
                            class="flex items-start gap-3 bg-red-600 text-white px-5 py-4 rounded-xl shadow-xl max-w-sm w-full mx-4">
                            <!-- Icon Error -->
                            <svg class="w-6 h-6 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                            </svg>

                            <div class="flex-1 text-sm">
                                {{ session('error') }}
                            </div>

                            <button @click="show = false" class="text-white/80 hover:text-white">
                                ✕
                            </button>
                        </div>
                    </div>
                @endif


                {{-- SUCCESS --}}
                @if (session('success'))
                    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2000)" x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center">
                        <div
                            class="flex items-start gap-3 bg-green-600 text-white px-5 py-4 rounded-xl shadow-xl max-w-sm w-full mx-4">
                            <!-- Icon Success -->
                            <svg class="w-6 h-6 mt-0.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>

                            <div class="flex-1 text-sm">
                                {{ session('success') }}
                            </div>

                            <button @click="show = false" class="text-white/80 hover:text-white">
                                ✕
                            </button>
                        </div>
                    </div>
                @endif



                @yield('content')
            </main>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>


    @stack('scripts')



</body>

</html>
