<!-- 1. Modal Tindak Lanjut (Action) -->
<div id="modalAction"
    class="fixed inset-0 bg-black/50 z-50 hidden flex items-end md:items-center justify-center transition-opacity">
    <div class="bg-white w-full md:w-125 md:rounded-3xl rounded-t-3xl p-6 transform translate-y-full transition-transform duration-300 max-h-[90vh] md:h-auto overflow-y-auto"
        id="cardAction">
        <div class="w-12 h-1 bg-gray-300 rounded-full mx-auto mb-6 md:hidden"></div>
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Tindak Lanjut</h3>
            <button onclick="closeModal('action')" class="text-gray-400 hover:text-gray-600"><i
                    class="fas fa-times text-xl"></i></button>
        </div>

        <div class="space-y-6">
            <!-- Jenis Tindakan -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Jenis
                    Tindakan</label>
                <div class="bg-gray-100 p-1 rounded-xl flex">
                    <button id="btnChat" onclick="toggleActionType('chat')"
                        class="flex-1 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition">Chat</button>
                    <button id="btnPhone" onclick="toggleActionType('phone')"
                        class="flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-white hover:shadow-sm transition">Telepon</button>
                </div>
            </div>

            <!-- Tombol Hubungi Wali -->
            <div class="flex items-center justify-between gap-2">
                <button id="btnContactAction" onclick="hubungiWali()"
                    class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-green-200">
                    <i class="fas fa-comment text-xl"></i> Hubungi Wali
                </button>

                @if ($siswa->getTotalTunggakan() < 0)
                    <button id="btnSendPayment" onclick="kirimTunggakan()"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-green-200">
                        <i class="fas fa-paper-plane text-xl"></i> Kirim Tunggakan
                    </button>
                @else
                    <button id="btnSendAppreciation" onclick="kirimApresiasi()"
                        class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-green-200">
                        <i class="fas fa-paper-plane text-xl"></i> Kirim Apresiasi
                    </button>
                @endif

            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">Catatan</label>
                <textarea name="notes" id="actionNotes"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm focus:ring-2 focus:ring-primaryLight outline-none"
                    rows="3" placeholder="Tuliskan hasil komunikasi..."></textarea>
            </div>

            <!-- Kesanggupan (Collapsible) - UPDATED -->
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <button onclick="toggleAccordion('kesanggupan')"
                    class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 transition text-left">
                    <span class="font-bold text-sm text-gray-700 flex items-center gap-2">
                        <i class="fas fa-file-signature text-primary"></i> Kesanggupan
                    </span>
                    <i id="iconkesanggupan" class="fas fa-chevron-down text-gray-400 text-sm transition-transform"></i>
                </button>
                <div id="contentkesanggupan" class="accordion-content bg-white">
                    <div class="p-4 space-y-4 border-t border-gray-100">
                        <div
                            class="bg-blue-50 p-3 rounded-lg border border-blue-100 flex items-start gap-2 text-xs text-blue-800">
                            <i class="fas fa-info-circle mt-0.5"></i>
                            <p>Kirimkan surat pernyataan kesanggupan ke wali murid.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-1">Tanggal Kesanggupan</label>
                            <input type="date" name="tanggal_kesanggupan" id="tanggal_kesanggupan"
                                class="w-full bg-gray-50 border border-gray-200 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-primaryLight outline-none">
                        </div>
                        <button onclick="sendAgreement()"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-xl font-bold flex items-center justify-center gap-2 text-sm transition">
                            <i class="fas fa-paper-plane"></i> Kirim Pernyataan Kesanggupan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Simpan -->
            <button onclick="saveAction()"
                class="w-full bg-primary text-white py-3 rounded-xl font-bold shadow-md hover:bg-blue-700 transition mt-2">Simpan
                Catatan</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // --- Tindak Lanjut Logic ---
        let currentActionType = 'chat';

        function toggleActionType(type) {
            currentActionType = type;
            const btnChat = document.getElementById('btnChat');
            const btnPhone = document.getElementById('btnPhone');
            const btnContact = document.getElementById('btnContactAction');

            if (type === 'chat') {
                btnChat.className = "flex-1 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition";
                btnPhone.className =
                    "flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-white hover:shadow-sm transition";
                btnContact.innerHTML = '<i class="fas fa-comment text-xl"></i> Hubungi Wali';
                btnContact.className =
                    "w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-green-200";
            } else {
                btnPhone.className = "flex-1 py-2 rounded-lg text-sm font-bold bg-primary text-white shadow-sm transition";
                btnChat.className =
                    "flex-1 py-2 rounded-lg text-sm font-bold text-gray-500 hover:bg-white hover:shadow-sm transition";
                btnContact.innerHTML = '<i class="fas fa-phone text-xl"></i> Hubungi Wali';
                btnContact.className =
                    "w-full bg-gray-800 hover:bg-gray-900 text-white py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition shadow-md shadow-gray-300";
            }
        }


        function getPhoneNumber(text) {
            // TODO: bagaimana jika ada 2 no hp wali?
            // contoh: 081234123 - ibu, 123412341 - ayah
            let phone = text.replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '62' + phone.slice(1);
            }
            if (phone.startsWith('62')) {
                return phone;
            }
            return null; // bukan nomor valid
        }

        function sendWhatsapp(content = '') {
            const phone = getPhoneNumber("{{ $siswa->phone }}");
            window.open(`https://wa.me/${phone}?text=${encodeURIComponent(content)}`, '_blank');
        }

        function hubungiWali() {
            let pesan = '';
            if (currentActionType === 'chat') {
                pesan =
                    'Assalamu’alaikum Bapak/Ibu, saya ingin menginformasikan mengenai pembayaran ananda {{ $siswa->nama }}. Mohon kesediaan Bapak/Ibu untuk merespon pesan ini. Terima kasih';
                sendWhatsapp(pesan);
            } else {
                sendWhatsapp();
            }
        }

        function formatTunggakanPerPeriode(tunggakan) {
            let result = {};

            // Group by periode
            tunggakan.forEach(category => {
                if (!result[category.periode]) {
                    result[category.periode] = [];
                }
                result[category.periode].push(category);
            });

            let text = '';

            Object.keys(result).forEach(periode => {
                text += `*Periode ${periode}*\n\n`;

                result[periode].forEach(category => {
                    if (category.summary.total_remaining === 0) return;

                    text += `${category.category_name}\n`;

                    category.items.forEach(item => {
                        if (item.remaining_balance < 0) {
                            text +=
                                `- ${item.unit_name} : ${formatRupiah(item.remaining_balance)}\n`;
                        }
                    });

                    // text +=
                    //     `Total ${category.category_name} : ${formatRupiah(category.summary.total_remaining)}\n`;
                });
                text += `______________________________ \n\n`;
            });

            return text.trim();
        }


        function kirimTunggakan() {
            // TODO: gunakan url khusus menampilkan detail pembayaran siswa jika diakses wali, menuju penanganan jika diakses petugas
            const detailTunggakan = formatTunggakanPerPeriode(@json($siswa->getKategoriBelumLunas()));
            const pesan =
                `Assalamu’alaikum Bapak/Ibu Wali {{ $siswa->nama }} \n\n` +
                `Berikut kami sampaikan rincian pembayaran belum lunas:\n\n` +
                detailTunggakan +
                `\n\n *Total Keseluruhan: ${formatRupiah({{ $siswa->getTotalTunggakan() }})}*` +
                `\n\n Informasi detail: {!! $urlUntukWali !!}` +
                `\n\nMohon kesediaan Bapak/Ibu untuk melakukan pembayaran atau konfirmasi kepada kami.\n\n` +
                `Terima kasih atas perhatian dan kerjasamanya`;

            sendWhatsapp(pesan);
        }

        function kirimApresiasi() {
            const pesan = `Assalamu’alaikum Bapak/Ibu Wali {{ $siswa->nama }} \n\n` +
                `Kami mengucapkan terima kasih atas pembayaran yang telah dilakukan. Dengan ini kami informasikan bahwa *pembayaran Anda telah kami terima dan dinyatakan LUNAS.* \n` +
                `Semoga kerja sama yang baik ini dapat terus terjalin.\n\n` +
                `Wassalamu’alaikum warahmatullahi wabarakatuh.`;
            sendWhatsapp(pesan);

        }


        function formatRupiah(number) {
            return 'Rp ' + Math.abs(number).toLocaleString('id-ID');
        }


        function sendAgreement() {
            const tanggal_kesanggupan = document.getElementById('tanggal_kesanggupan').value;
            const bolehKirimKesanggupan = @json($penangananTerakhir && $penangananTerakhir->status !== 'selesai');
            if (!tanggal_kesanggupan) {
                showToast('Tanggal kesanggupan harus diisi', 'error');
                return;
            }

            if (!bolehKirimKesanggupan) {
                showToast('Follow up wali terlebih dahulu', 'error');
                return;
            }

            fetch("{{ route('penanganan.kesanggupan') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        penanganan_id: {{ $penangananTerakhir->id ?? 'null' }},
                        tanggal_kesanggupan: tanggal_kesanggupan
                    })
                })
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        showToast('Gagal mengirim kesanggupan', 'error');
                        return;
                    }

                    const pesan = `Assalamu’alaikum Bapak/Ibu Wali {{ $siswa->nama }} 🙏

Mohon kesediaan Bapak/Ibu untuk mengisi Form Kesanggupan Pembayaran melalui link berikut:

${res.link}

Terima kasih atas perhatian dan kerja samanya 🙏`;

                    sendWhatsapp(pesan);
                    showToast('Pernyataan kesanggupan berhasil dikirim', 'success');
                })
                .catch(() => {
                    showToast('Terjadi kesalahan sistem', 'error');
                });
        }

        function saveAction() {
            const catatan = document.getElementById('actionNotes').value;
            if (!catatan) {
                showToast('Catatan tindakan harus diisi', 'error');
                return;
            }
            // simpan penanganan ke database
            fetch("{{ route('penanganan.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        id_siswa: {{ $siswa->id }},
                        jenis_penanganan: currentActionType,
                        catatan: catatan
                    })
                }).then(async response => {
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
                    showToast(error.message ?? 'Terjadi kesalahan', 'error');
                });


        }
    </script>
@endpush
