// QUICK START - Sinkronisasi Pembayaran Siswa

## 🎯 3 Langkah Sederhana

### 1️⃣ Jalankan Queue Worker

```bash
# Terminal 1 - Laravel Server
cd C:\laragon\www\bantubayar
php artisan serve

# Terminal 2 - Queue Worker
cd C:\laragon\www\bantubayar
php artisan queue:work database --queue=sync-pembayaran --tries=3 --timeout=600
```

### 2️⃣ Buka Halaman Sinkronisasi

-   Akses: http://localhost:8000/admin/siswa
-   Klik tombol: **"Sinkron Pembayaran"**

### 3️⃣ Mulai Proses

-   Di halaman `/admin/sync-pembayaran`
-   Klik tombol: **"Mulai Sinkronisasi"** 🟢
-   Monitor progress di halaman

---

## 📊 Halaman Sinkronisasi Pembayaran

```
┌─────────────────────────────────────────────────────────────┐
│  Sinkronisasi Pembayaran Siswa                    [Kembali] │
│  Monitor dan kelola proses sinkronisasi pembayaran...        │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Status: 🔄 Sedang Berjalan                                  │
│                                                               │
│  [▶ Mulai] [⏹ Batalkan] [🔄 Reset]                          │
│                                                               │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Persentase Selesai                           75.5%      ││
│  │ ████████████████████░░░░░░ 75.5%                        ││
│  │                                                           ││
│  │ ┌────────┬────────┬────────┐                            ││
│  │ │ Total  │Berhasil│ Gagal  │                            ││
│  │ │  1000  │  755   │   15   │                            ││
│  │ └────────┴────────┴────────┘                            ││
│  │                                                           ││
│  │ Diproses: 770 / 1000                                     ││
│  │ Estimasi Waktu: 2m 15s                                   ││
│  │ Status: Sedang Berjalan...                               ││
│  └─────────────────────────────────────────────────────────┘│
│                                                               │
│  Log Aktivitas:                                               │
│  [12:35:42] ✓ Sinkronisasi selesai                          │
│  [12:35:38] 📡 Polling dimulai                              │
│  [12:35:35] ▶ Sinkronisasi dimulai                          │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## ✨ Fitur Utama

✅ **Real-time Monitoring**

-   Progress bar dengan persentase
-   Counter berhasil/gagal
-   Estimasi waktu tersisa

✅ **Kontrol Penuh**

-   Tombol Mulai untuk memulai
-   Tombol Batalkan untuk berhenti
-   Tombol Reset untuk clear progress

✅ **Informasi Detail**

-   Status sinkronisasi (Siap/Sedang Berjalan/Selesai)
-   Statistik lengkap (Total, Berhasil, Gagal)
-   Log aktivitas real-time

✅ **User-Friendly**

-   Button otomatis enable/disable
-   Auto-stop polling saat selesai
-   Dapat ditutup & dibuka kembali

---

## 🔍 Monitoring Progress

Polling otomatis setiap 500ms mengupdate:

-   Progress bar (0% - 100%)
-   Counter siswa yang sudah diproses
-   Counter siswa yang berhasil/gagal
-   Estimasi waktu tersisa

Saat mencapai 100%:

-   Polling berhenti otomatis
-   Status berubah menjadi "Selesai"
-   Notifikasi ditampilkan
-   Tombol "Mulai" enabled kembali

---

## 🛑 Membatalkan Sinkronisasi

1. Klik tombol **"Batalkan"** (warna merah)
2. Konfirmasi di dialog
3. Proses akan dihentikan
4. Cache akan di-clear
5. Progress bar reset ke 0%

---

## 🔄 Reset Progress

1. Klik tombol **"Reset"** (warna abu-abu)
2. Semua counter akan direset
3. Progress bar kembali ke 0%
4. Siap untuk sinkronisasi baru

---

## 🐛 Troubleshooting

### Progress tidak bergerak?

-   Check queue worker di terminal → pastikan RUNNING
-   Run: `php artisan queue:failed`
-   Lihat error di: `storage/logs/laravel.log`

### Data pembayaran tidak terupdate?

-   Check API eksternal connectivity
-   Check failed jobs untuk error
-   Verify kolom `pembayaran` di tabel `siswa`

### Tombol tidak responsif?

-   Refresh halaman (F5)
-   Clear browser cache
-   Buka di browser tab baru

---

## 📋 Checklist Sebelum Mulai

-   [ ] Queue worker running (Terminal 2)
-   [ ] Laravel server running (Terminal 1)
-   [ ] Database connected
-   [ ] Cache driver = database atau redis (bukan array!)
-   [ ] Tabel `siswa` ada kolom `pembayaran`
-   [ ] API eksternal responsive

---

## 📞 Support

Jika ada masalah:

1. Check documentation: `QUEUE_SETUP_NEW.md`
2. Check laravel log: `storage/logs/laravel.log`
3. Run: `php artisan queue:failed` untuk failed jobs
4. Clear cache: `php artisan cache:clear`

---

Selamat! 🎉 Halaman sinkronisasi pembayaran sudah siap digunakan!
