# Setup Queue Untuk Sinkronisasi Pembayaran Siswa

## Konfigurasi

Queue sudah dikonfigurasi menggunakan **database driver** di `config/queue.php`.

Cache key yang digunakan:

-   `sync_pembayaran_total` - Total siswa yang akan disinkronisasi
-   `sync_pembayaran_processed` - Jumlah siswa yang sudah diproses
-   `sync_pembayaran_failed` - Jumlah siswa yang gagal
-   `sync_pembayaran_status` - Status proses (running/completed)

## Cara Menjalankan Queue Worker

### Development (Testing)

**Terminal 1 - Jalankan Laravel server:**

```bash
php artisan serve
```

**Terminal 2 - Jalankan queue worker:**

```bash
php artisan queue:work database --queue=sync-pembayaran --tries=3 --timeout=600
```

Flag penjelasan:

-   `--queue=sync-pembayaran` → Hanya proses queue dengan nama "sync-pembayaran"
-   `--tries=3` → Retry job maksimal 3 kali jika gagal
-   `--timeout=600` → Timeout setiap job adalah 600 detik (10 menit)

### Production

Jalankan queue worker di background menggunakan Supervisor atau PM2.

## Endpoint & Route

### Halaman Web

-   **GET** `/admin/sync-pembayaran` → Halaman sinkronisasi pembayaran (UI lengkap)

### API Endpoint

-   **POST** `/admin/sync-pembayaran/start` → Mulai sinkronisasi
-   **POST** `/admin/sync-pembayaran/cancel` → Batalkan sinkronisasi
-   **GET** `/admin/sync-pembayaran/progress` → Ambil progress (real-time)
-   **POST** `/admin/sync-pembayaran/reset` → Reset progress

## Alur Kerja

### 1. User membuka halaman sinkronisasi

-   Navigasi ke `/admin/siswa` → Klik tombol "Sinkron Pembayaran"
-   Atau langsung ke `/admin/sync-pembayaran`
-   Halaman menampilkan:
    -   Status sinkronisasi (Siap/Sedang Berjalan)
    -   Progress bar & persentase
    -   Tombol kontrol (Mulai, Batalkan, Reset)
    -   Log aktivitas real-time
    -   Statistik (Total, Berhasil, Gagal)

### 2. User klik tombol "Mulai Sinkronisasi"

-   Request dikirim ke `/admin/sync-pembayaran/start`
-   Controller set status cache → "running"
-   Dispatch `DispatchSyncPembayaranJob`

### 3. DispatchSyncPembayaranJob berjalan

-   Hitung total siswa
-   Set cache: total, processed=0, failed=0
-   Loop siswa per 100 chunk
-   Dispatch `SyncPembayaranSiswaJob` untuk setiap siswa

### 4. Queue worker memproses SyncPembayaranSiswaJob

-   Ambil data pembayaran via API eksternal
-   Update kolom `pembayaran` (JSON) di database
-   Increment `sync_pembayaran_processed` counter
-   Jika gagal, increment `sync_pembayaran_failed`

### 5. Frontend polling progress

-   Polling `/admin/sync-pembayaran/progress` setiap 500ms
-   Update progress bar & statistik
-   Tampilkan estimasi waktu tersisa
-   Ketika progress 100%, polling stop dan tampil notifikasi

## Fitur Halaman Sinkronisasi

✅ **Real-time Monitoring**

-   Progress bar dengan persentase
-   Counter berhasil/gagal
-   Estimasi waktu tersisa

✅ **Kontrol Penuh**

-   Tombol Mulai untuk memulai sinkronisasi
-   Tombol Batalkan untuk menghentikan proses
-   Tombol Reset untuk mereset progress

✅ **Informasi Detail**

-   Status sinkronisasi (Siap/Sedang Berjalan/Selesai)
-   Statistik: Total, Berhasil, Gagal, Diproses
-   Log aktivitas real-time

✅ **User Experience**

-   Button disabled saat proses berjalan
-   Auto-stop polling saat selesai
-   Notifikasi saat proses selesai
-   Dapat dipantau di halaman berbeda

## Troubleshooting

### Queue jobs tidak tereksekusi

1. Pastikan queue worker sudah running
2. Check job di database table `jobs`
3. Cek failed jobs di table `failed_jobs`
4. Run: `php artisan queue:failed` untuk melihat failed jobs

### Progress tidak update

1. Pastikan cache connection bekerja (bukan "array" cache)
2. Run: `php artisan cache:clear`
3. Check Redis/Database connection

### API timeout atau error

1. Naikkan `--timeout` value di queue worker
2. Check API eksternal apakah masih berjalan
3. Run: `php artisan queue:failed` untuk melihat error detail
4. Check log di `storage/logs/laravel.log`

### Button "Mulai Sinkronisasi" tidak bisa diklik

1. Proses sinkronisasi sedang berjalan
2. Klik "Batalkan" terlebih dahulu
3. Atau tunggu sampai 100% selesai

## Database Tables

Pastikan sudah ada:

-   `jobs` - Queue jobs
-   `failed_jobs` - Failed jobs log
-   `siswa` - Siswa dengan kolom `pembayaran` (JSON)

Buat migration jika belum:

```bash
php artisan queue:failed-table
php artisan migrate
```

## Environment Variables

`.env` configuration:

```
QUEUE_CONNECTION=database
DB_QUEUE_CONNECTION=mysql
DB_QUEUE_TABLE=jobs
DB_QUEUE=sync-pembayaran
```

## Catatan Penting

1. **Queue worker harus selalu running** - Tanpa queue worker, job tidak akan diproses
2. **Cache driver yang tepat** - Gunakan Redis atau Database, jangan "array"
3. **Database connection stabil** - API eksternal membutuhkan koneksi database yang stabil
4. **Timeout yang cukup** - Sinkronisasi pembayaran bisa memakan waktu lama
5. **Monitoring teratur** - Check log dan failed jobs secara berkala
