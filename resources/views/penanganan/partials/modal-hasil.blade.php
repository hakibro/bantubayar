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
                <select
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none font-medium">
                    <option>Lunas</option>
                    <option>Isi Saldo</option>
                    <option>Cicilan</option>
                    <option>Tidak Ada Respon</option>
                    <option>HP Tidak Aktif</option>
                </select>
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Catatan
                    Hasil</label>
                <textarea
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
