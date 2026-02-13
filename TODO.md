### Todo:

- ubah logika simpan aksi agar update juga timestamp penanganan

### Done:

- index list penanganan dengan filter by [status, hasil, sudah ditangani, created_at, kesanggupan], hapus duplikat nama siswa karena penanganan > 1, update tampilan list,

Admin:

- detailkan sync siswa: update/delete siapa, field apa yang di update.

- tambahkan field bukti pembayaran jika hasil = lunas/isi saldo.

Siswa:

- detailkan sync siswa: update/delete siapa, field apa yang di update.
- format pesan chat wa

Petugas:

- hapus bukti pembayaran krn sudah digantikan oleh validasi sistem.

Admin:

Petugas/Bendahara:

- Dashboard
    - card diatas bisa di klik sebagai filter daftar penanganan
- Daftar Penanganan

Penanganan:

- tambah history jenis penanganan:
    - chat -> tunggu -> telepon -> tunggu -> telepon ulang -> update penanganan
- bandingkan [tagihan awal, saldo awal] <-> [tagihan awal, saldo sekarang]
- set auto hasil:
    - lunas:
        - status: selesai
        - jika semua tagihan sudah dibayar (api sync sekarang)
    - cicilan:
        - status: selesai
        - jika ada beberapa tagihan yang dilunasi
    - isi saldo:
        - status: selesai
        - jika saldo sekarang > saldo awal
    - rekomendasi: jika [tagihan awal, saldo awal] tetap
        - status: menunggu_tindak_lanjut
        - set tanggal rekomendasi
        - kirim link pernyataan rekomendasi ke wali berisi tanggal rekomendasi
        - wali input biaya rekomendasi dan menyetujui pernyataan rekomendasi
    - tidak_ada_respon:
        - status: selesai
        - disable jika jenis penanganan belum sampai telepon ulang
        - jika tidak ada respon wali dari chat / telepon, tapi wa masuk centang 2 / telepon wa terhubung.

    - hp_tidak_aktif:
        - status: menunggu_tindak_lanjut
        - jika tidak ada no. hp di data siswa:
            - tampilkan view update no. hp via api

        - jika ada no.hp di data siswa:
            - disable jika jenis penanganan belum sampai telepon ulang
            - jika wa tidak masuk centang 1 & telepon wa tidak terhubung.
            - set catatan no. hp tidak aktif
            - tampilkan view update no. hp via api

- ringkaskan form wa
- filter yg belum bayar :
    - menambahkan cron job auto sync all malam hari
- format telepon
- form rekom (isi saldo / bayar nanti)
- hapus jenis penanganan visit
- update no. hp via api?

Siswa:

- tambah saldo: buat tabel siswa_saldo, sync ikutkan dengan pembayaran
- Pindah (refactor) bendahara index & show ke siswa agar bisa digunakan juga oleh petugas cs, dan agar rapi
- List siswa = lunas/belum
- Detail/show siswa: pembayaran saat ini + history penanganan

Admin:

- form visit

kendala:

- tidak ada validasi sudah kirim pesan dan sudah telepon krn pake wa:
    - status penanganan tidak bisa otomatis (menunggu respon, sudah direspon, telepon x kali, menunggu tindak lanjut, selesai) - alternatif (menunggu respon, menunggu tindak lanjut, selesai)
