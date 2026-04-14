<x-guest-layout>
    <style>
        /* Gradasi Hijau yang cocok dengan logo badge hijau Anda */
        .brand-gradient {
            background-color: #f0fdf4;
            /* Emerald super light */
            background-image: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 40%, #bbf7d0 100%);
        }
    </style>

    <div
        class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden flex flex-col md:flex-row max-w-5xl w-full min-h-[550px] my-auto">

        <div
            class="hidden md:flex md:w-5/12 brand-gradient p-12 flex-col items-center justify-center relative border-r border-green-50">
            <div class="z-20 text-center">
                <a href="/">
                    <img src="{{ asset('assets/img/logo-250px.png') }}" class="h-48 w-auto object-contain drop-shadow-xl"
                        alt="Logo">
                </a>

            </div>

            <div class="absolute top-0 left-0 w-full h-full opacity-20 pointer-events-none">
                <div class="absolute top-[-10%] left-[-10%] w-64 h-64 rounded-full bg-green-300 blur-3xl"></div>
                <div class="absolute bottom-[-10%] right-[-10%] w-64 h-64 rounded-full bg-emerald-200 blur-3xl"></div>
            </div>
        </div>

        <div class="w-full md:w-7/12 p-8 md:p-14 flex flex-col justify-center bg-white">
            <div class="md:hidden mb-8 text-center">
                <img src="{{ asset('assets/img/logo-250px.png') }}" class="h-20 w-auto mx-auto" alt="Logo">
            </div>

            <h2 class="text-3xl font-extrabold text-gray-800 mb-2">Masuk Akun</h2>
            <p class="text-gray-500 mb-8 text-sm">Silakan masukkan detail akun Anda untuk melanjutkan akses.</p>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all shadow-sm">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-bold text-gray-700">Password</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs text-green-700 hover:underline font-semibold">Lupa Password?</a>
                        @endif
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all shadow-sm pr-12">
                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-500 hover:text-green-600 focus:outline-none">
                            <!-- Eye icon (closed) -->
                            <svg id="eyeIconClosed" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                            </svg>
                            <!-- Eye icon (open) - hidden by default -->
                            <svg id="eyeIconOpen" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <div class="flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <label for="remember_me" class="ms-2 text-sm text-gray-500 italic">Tetap masuk di perangkat
                        ini</label>
                </div>

                <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-100 transition-all active:scale-95 text-lg">
                    Masuk Sekarang
                </button>
            </form>

            @if (Route::has('register'))
                <p class="text-center text-sm text-gray-500 mt-8">
                    Belum punya akun? <a href="{{ route('register') }}"
                        class="text-green-700 font-bold hover:underline">Daftar</a>
                </p>
            @endif
        </div>
    </div>

    <script>
        // Toggle show/hide password
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('togglePassword');
        const eyeIconClosed = document.getElementById('eyeIconClosed');
        const eyeIconOpen = document.getElementById('eyeIconOpen');

        toggleBtn.addEventListener('click', function() {
            // Toggle type
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle icons
            eyeIconClosed.classList.toggle('hidden');
            eyeIconOpen.classList.toggle('hidden');
        });
    </script>
</x-guest-layout>
