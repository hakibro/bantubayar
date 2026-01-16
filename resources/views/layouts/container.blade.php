<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <!-- Scripts -->
    @vite(['resources/js/app.js'])

</head>

<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center p-4 bg-gray-100">
        @yield('content')
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

    @stack('scripts')
    <script>
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
    </script>
</body>

</html>
