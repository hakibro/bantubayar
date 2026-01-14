<!-- 1. Modal Tindak Lanjut (Action) -->
<div id="modalUpdatehp"
    class="fixed inset-0 bg-black/50 z-50 hidden flex items-end md:items-center justify-center transition-opacity">
    <div class="bg-white w-full md:w-[500px] md:rounded-3xl rounded-t-3xl p-6 transform translate-y-full transition-transform duration-300 max-h-[90vh] md:h-auto overflow-y-auto"
        id="cardUpdatehp">
        <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-6 md:hidden"></div>
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Update No. HP Wali</h3>
            <button onclick="closeModal('Updatehp')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>

        <div class="space-y-6">
            <!-- Toggle Wali -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    Wali Murid
                </label>

                <div class="flex bg-gray-100 rounded-xl p-1">
                    <button id="btnIbu" onclick="toggleWaliType('ibu')"
                        class="flex-1 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition">
                        Ibu
                    </button>

                    <button id="btnAyah" onclick="toggleWaliType('ayah')"
                        class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-white hover:bg-primary hover:shadow-sm transition">
                        Ayah
                    </button>
                </div>
            </div>

            <!-- Input No HP -->
            <div class="mt-4">
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">
                    Nomor HP Wali
                </label>
                <input type="tel" id="phoneInput" placeholder="081234..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm
           text-blue-600 font-semibold
           placeholder-gray-400
           focus:ring-2 focus:ring-primaryLight outline-none">
            </div>

            <!-- Simpan -->
            <button onclick="savePhone()"
                class="w-full bg-primary text-white py-3 rounded-xl font-bold shadow-md hover:bg-blue-700 transition mt-4">
                Simpan Nomor HP
            </button>

        </div>
    </div>
</div>


@push('scripts')
    <script>
        let currentWaliType = 'ibu';

        function toggleWaliType(type) {
            currentWaliType = type;

            const btnIbu = document.getElementById('btnIbu');
            const btnAyah = document.getElementById('btnAyah');

            if (type === 'ibu') {
                btnIbu.className =
                    "flex-1 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition";
                btnAyah.className =
                    "flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-white hover:bg-primary hover:shadow-sm transition";
            } else {
                btnAyah.className =
                    "flex-1 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition";
                btnIbu.className =
                    "flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:text-white hover:bg-primary hover:shadow-sm transition";
            }
        }



        function savePhone() {
            const phone = document.getElementById('phoneInput').value;

            console.log({
                id_siswa: @json($siswa->id),
                wali: currentWaliType, // ibu / ayah
                phone: phone
            });

            // TODO: kirim ke backend
            fetch("{{ route('penanganan.update_phone') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        id_siswa: @json($siswa->id),
                        wali: currentWaliType,
                        phone: phone
                    })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    location.reload();
                })
                .catch(error => {
                    console.error(error);
                    showToast(error.message, 'error');
                });

            closeModal('updatehp');
            showToast('Nomor HP siswa berhasil diperbarui', 'success');
        }
    </script>
@endpush
