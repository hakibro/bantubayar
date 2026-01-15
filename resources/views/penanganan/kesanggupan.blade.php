<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="dummy_token">

    <title>Form Kesanggupan Pembayaran</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- FontAwesome untuk Ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <!-- Google Fonts: Plus Jakarta Sans untuk tampilan modern -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Transisi halus untuk elemen interaktif */
        .transition-all-300 {
            transition: all 0.3s ease-in-out;
        }

        /* Custom Scrollbar untuk elemen suggestions jika terlalu panjang */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Animasi Modal */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>

<body
    class="bg-gradient-to-br from-blue-50 to-indigo-100 text-gray-800 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg bg-white rounded-3xl shadow-xl overflow-hidden relative animate-fade-in">

        <!-- Header Section dengan Background Accent -->
        <div class="bg-blue-600 px-8 py-6 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-blue-500 rounded-full opacity-50 blur-2xl">
            </div>
            <div class="relative z-10 flex items-center space-x-4">
                <div class="bg-blue-500/30 p-3 rounded-full backdrop-blur-sm border border-blue-400/30">
                    <i class="fa-solid fa-file-contract text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold leading-tight">Kesanggupan Pembayaran</h2>
                    <p class="text-blue-100 text-xs mt-1">Lengkapi formulir di bawah ini</p>
                </div>
            </div>
        </div>

        <div class="p-8">

            <!-- Info Tanggal (Simulasi Data Server) -->
            <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-2xl p-4 mb-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-indigo-100 text-indigo-600 w-10 h-10 rounded-xl flex items-center justify-center">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Tanggal</p>
                        <p class="font-bold text-gray-800" id="displayDate">
                            <!-- Akan diisi oleh Javascript -->
                        </p>
                    </div>
                </div>
            </div>

            <form id="commitmentForm" autocomplete="off">

                <!-- Input Nominal Section -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Masukkan nominal sesuai kemampuan Bapak/Ibu

                    </label>

                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-medium">Rp</span>
                        </div>

                        <!-- Input Tampilan -->
                        <input type="text" id="nominal_display" required placeholder="0"
                            class="w-full pl-12 pr-12 py-4 rounded-2xl border border-gray-200 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all-300 outline-none text-gray-800 font-medium text-lg placeholder-gray-400">

                        <!-- Tombol Hapus -->
                        <button type="button" id="clearBtn"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-red-500 cursor-pointer transition-colors hidden">
                            <i class="fa-solid fa-circle-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Helper Text -->
                    <div class="flex justify-between items-center mt-2">
                        <p class="text-xs text-gray-500">
                            <i class="fa-solid fa-circle-info mr-1"></i> Masukkan nominal angka saja.
                        </p>
                        <!-- Hidden Input for Logic -->
                        <input type="hidden" name="nominal" id="nominal" value="">
                    </div>
                </div>

                <!-- Quick Options (Chips) -->
                <div class="mb-8">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Saran Nominal</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" onclick="setAmount(500000)"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all-300">
                            500 Rb
                        </button>
                        <button type="button" onclick="setAmount(1000000)"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all-300">
                            1 Juta
                        </button>
                        <button type="button" onclick="setAmount(2500000)"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all-300">
                            2.5 Juta
                        </button>
                        <button type="button" onclick="setAmount(5000000)"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-blue-50 hover:text-blue-600 hover:border-blue-200 transition-all-300">
                            5 Juta
                        </button>
                    </div>
                </div>

                Data yang diisi akan digunakan sebagai dasar kesepakatan pembayaran

                <!-- Submit Button -->
                <button type="submit" id="submitBtn"
                    class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white py-4 rounded-2xl font-bold text-lg shadow-lg shadow-blue-600/30 transition-all-300 flex items-center justify-center gap-2 group">
                    <span>Kirim Kesanggupan</span>
                    <i class="fa-solid fa-paper-plane group-hover:translate-x-1 transition-transform"></i>
                </button>

            </form>
        </div>
    </div>

    <!-- MODAL SUKSES (Hidden by default) -->
    <div id="thankyouModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop">
        </div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-sm opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    id="modalPanel">

                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 text-center">
                        <div
                            class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-green-100 mb-5 animate-bounce">
                            <i class="fa-solid fa-check text-3xl text-green-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold leading-6 text-gray-900 mb-2" id="modal-title">Berhasil!</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Terima kasih atas kesanggupan pembayaran Anda. Data telah kami terima dan sedang
                                diproses lebih lanjut.
                            </p>
                            <!-- Display Summary in Modal -->
                            <div class="mt-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <p class="text-xs text-gray-400 uppercase font-bold">Nominal Terkirim</p>
                                <p class="text-lg font-bold text-blue-600" id="modalNominal">Rp 0</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" onclick="closeModal()"
                            class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-3 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-full transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT LOGIC -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Set Tanggal Otomatis 
            const dateElement = document.getElementById('displayDate');
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const day = new Date(@json($kesanggupan->tanggal));
            console.log(day);
            dateElement.textContent = day.toLocaleDateString('id-ID', options);

            // 2. Logic Input Format Rupiah
            const displayInput = document.getElementById('nominal_display');
            const realInput = document.getElementById('nominal');
            const clearBtn = document.getElementById('clearBtn');

            displayInput.addEventListener('input', function(e) {
                // Hapus karakter selain angka
                let value = this.value.replace(/\D/g, '');

                if (!value) {
                    realInput.value = '';
                    clearBtn.classList.add('hidden');
                    return;
                }

                // Simpan nilai asli
                realInput.value = value;

                // Format ke Rupiah
                this.value = new Intl.NumberFormat('id-ID').format(value);

                // Tampilkan tombol hapus
                clearBtn.classList.remove('hidden');
            });

            // Tombol Hapus Input
            clearBtn.addEventListener('click', () => {
                displayInput.value = '';
                realInput.value = '';
                displayInput.focus();
                clearBtn.classList.add('hidden');
            });

            // 3. Logic Quick Select (Saran Nominal)
            window.setAmount = function(amount) {
                // Efek visual "click" pada input
                displayInput.focus();

                // Set value
                realInput.value = amount;
                displayInput.value = new Intl.NumberFormat('id-ID').format(amount);
                clearBtn.classList.remove('hidden');
            };

            // 4. Logic Submit Form
            const form = document.getElementById('commitmentForm');
            const submitBtn = document.getElementById('submitBtn');
            const originalBtnContent = submitBtn.innerHTML;

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validasi Sederhana
                if (!realInput.value || parseInt(realInput.value) <= 0) {
                    // Shake effect pada input jika kosong
                    displayInput.classList.add('ring-4', 'ring-red-500/20', 'border-red-400');
                    setTimeout(() => displayInput.classList.remove('ring-4', 'ring-red-500/20',
                        'border-red-400'), 500);
                    return;
                }

                // Ubah tombol jadi loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin"></i> Mengirim...`;
                submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

                // Simulasi Request ke Server (Delay 1.5 detik)
                setTimeout(() => {
                    // Tampilkan Modal Sukses
                    showModal();

                    // Reset Form & Tombol
                    form.reset();
                    realInput.value = '';
                    clearBtn.classList.add('hidden');

                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');

                }, 1500);
            });
        });

        // Modal Logic
        const modal = document.getElementById('thankyouModal');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalPanel = document.getElementById('modalPanel');

        function showModal() {
            // Set nominal di modal
            const nominal = document.getElementById('nominal').value;
            document.getElementById('modalNominal').textContent = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(nominal);

            modal.classList.remove('hidden');
            // Animasi masuk
            setTimeout(() => {
                modalBackdrop.classList.remove('opacity-0');
                modalPanel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                modalPanel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            }, 10);
        }

        function closeModal() {
            // Animasi keluar
            modalBackdrop.classList.add('opacity-0');
            modalPanel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            modalPanel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300); // Sesuaikan durasi transisi CSS
        }
    </script>
</body>

</html>
