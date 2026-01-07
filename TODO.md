### Todo:

Admin:

Petugas/Bendahara:

-   Dashboard
    -   card diatas bisa di klik sebagai filter daftar penanganan
-   Daftar Penanganan

Penanganan:

-   tambah history jenis penanganan
-   hapus bukti pembayaran
-   update view penanganan: bandingkan tagihan + saldo awal <-> tagihan + saldo saat ini
-   ringkaskan form wa
-   jika tidak ada no. hp, update nomor hp via api. status = menunggu_tindak_lanjut.
-   filter yg belum bayar :
    -   menambahkan cron job auto sync all malam hari
-   format telepon
-   form rekom (isi saldo / bayar nanti)
-   hapus jenis penanganan visit
-   update no. hp via api?

Siswa:

-   List siswa = lunas/belum
-   Detail siswa: pembayaran saat ini + history penanganan

Admin:

-   form visit

kendala:

-   tidak ada validasi sudah kirim pesan dan sudah telepon krn pake wa:
    -   status penanganan tidak bisa otomatis (menunggu respon, sudah direspon, telepon x kali, menunggu tindak lanjut, selesai) - alternatif (menunggu respon, menunggu tindak lanjut, selesai)

### Done:

Admin:

-   detailkan sync siswa: update/delete siapa, field apa yang di update.

-   tambahkan field bukti pembayaran jika hasil = lunas/isi saldo.

Siswa:

-   detailkan sync siswa: update/delete siapa, field apa yang di update.
-   format pesan chat wa
