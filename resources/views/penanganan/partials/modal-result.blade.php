<!-- 2. Modal Hasil (Result) -->
<div id="modalResult"
    class="fixed inset-0 bg-black/50 z-50 hidden flex items-end md:items-center justify-center transition-opacity">
    <div class="bg-white w-full md:w-[500px] md:rounded-3xl rounded-t-3xl p-6 transform translate-y-full transition-transform duration-300"
        id="cardResult">
        <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-6 md:hidden"></div>
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Hasil Penanganan</h3>
            <button onclick="closeModal('result')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>

        <div class="space-y-6">
            <!-- Toggle Hasil -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Hasil</label>
                <select name="hasilPenanganan" id="hasilPenanganan"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none font-medium">
                    <option value="lunas">Lunas</option>
                    <option value="isi_saldo">Isi Saldo</option>
                    <option value="cicilan">Cicilan</option>
                    <option value="tidak_ada_respon">Tidak Ada Respon</option>
                    <option value="hp_tidak_aktif">HP Tidak Aktif</option>
                </select>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Catatan
                    Hasil</label>
                <textarea name="catatanHasil" id="catatanHasil"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none"
                    rows="3" placeholder="Detail hasil..."></textarea>
            </div>

            <!-- Rating Wali -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Rating Wali
                    Murid</label>
                <div class="flex gap-2 text-2xl text-gray-200 star-rating justify-center py-2" id="starContainer">
                    <i class="fas fa-star" onclick="rate(1)"></i>
                    <i class="fas fa-star" onclick="rate(2)"></i>
                    <i class="fas fa-star" onclick="rate(3)"></i>
                    <i class="fas fa-star" onclick="rate(4)"></i>
                    <i class="fas fa-star" onclick="rate(5)"></i>
                </div>
            </div>

            <!-- Simpan -->
            <button onclick="saveResult()"
                class="w-full bg-primary text-white py-3 rounded-xl font-bold shadow-md hover:bg-blue-700 transition mt-2">Simpan
                Status</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // --- Hasil Logic ---
        let currentRating = 0;

        function rate(n) {
            currentRating = n;
            const stars = document.getElementById('starContainer').children;
            for (let i = 0; i < 5; i++) {
                if (i < n) stars[i].classList.add('active');
                else stars[i].classList.remove('active');
            }
        }
        const hasPenanganan = @json($siswa->penangananAktif());
        const penangananId = @json(optional($penangananTerakhir)->id)

        function saveResult() {
            const catatan = document.getElementById('catatanHasil').value;

            if (!catatan) {
                showToast('Catatan harus diisi', 'warning');
                return;
            }

            if (currentRating === 0) {
                showToast('Silahkan Beri Rating Wali', 'warning');
                return;
            }

            if (!hasPenanganan) {
                showToast('Lakukan tindakan penanganan sebelum menyimpan hasil', 'warning');
                return;
            }

            fetch("{{ route('penanganan.save_hasil') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    },
                    body: JSON.stringify({
                        id_penanganan: penangananId,
                        hasil: document.getElementById('hasilPenanganan').value,
                        catatan: catatan,
                        rating: currentRating
                    })
                })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok) throw data;
                    return data;
                })
                .then(data => {
                    showToast(data.message ?? 'Berhasil', 'success');
                    closeModal('result');
                    setTimeout(() => location.reload(), 800);
                })
                .catch(error => {
                    if (error.action_required === 'update_nomor_hp') {
                        showToast(error.message, 'warning');
                        closeModal('result');
                        openModal('updatehp');
                        return;
                    }

                    showToast(error.message ?? 'Terjadi kesalahan', 'error');
                });
        }
    </script>
@endpush
