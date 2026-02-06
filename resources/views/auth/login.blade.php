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
                    <input type="password" name="password" required
                        class="w-full px-4 py-3.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition-all shadow-sm">
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
</x-guest-layout>
