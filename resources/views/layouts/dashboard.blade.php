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
                @yield('content')
            </main>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')



</body>

</html>
