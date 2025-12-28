<div x-show="openQrPesan" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    @click.self="openQrPesan = false" style="display: none;">

    <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 text-center">
        <h3 class="text-lg font-semibold mb-2">
            Kirim Pesan WhatsApp
        </h3>

        <p class="text-sm text-gray-600 mb-4">
            Scan QR untuk membuka WhatsApp dengan pesan
        </p>

        <div class="flex justify-center mb-4">
            {!! QrCode::format('svg')->size(220)->margin(2)->generate($waUrl) !!}
        </div>

        <p class="text-xs text-gray-500 mb-4">
            Nomor: {{ $phone }}
        </p>

        <div class="flex gap-2">
            <a href="{{ $waUrl }}" target="_blank"
                class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                Buka WhatsApp
            </a>

            <button @click="openQrPesan = false"
                class="flex-1 border border-gray-300 py-2 rounded-lg hover:bg-gray-100 transition">
                Tutup
            </button>
        </div>
    </div>
</div>
