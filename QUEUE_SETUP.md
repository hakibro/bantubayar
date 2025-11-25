# Setup Queue Untuk Sinkronisasi Pembayaran Siswa

## Konfigurasi

Queue sudah dikonfigurasi menggunakan **database driver** di `config/queue.php`.

Cache key yang digunakan:

-   `sync_pembayaran_total` - Total siswa yang akan disinkronisasi
-   `sync_pembayaran_processed` - Jumlah siswa yang sudah diproses
-   `sync_pembayaran_failed` - Jumlah siswa yang gagal

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

## Endpoint

-   **POST/GET** `/admin/siswa/sync-pembayaran-siswa` → Mulai sinkronisasi pembayaran
-   **GET** `/admin/siswa/get-progress-pembayaran` → Ambil progress (%) real-time

## Alur Kerja

1. User klik tombol "Mulai Sinkron Pembayaran" di `/admin/siswa`
2. Frontend memanggil `/admin/siswa/sync-pembayaran-siswa`
3. Controller memanggil `SiswaService::syncPembayaranSiswa()`
4. Service menjalankan `DispatchSyncPembayaranJob::dispatch()`
5. Job ini:
    - Hitung total siswa
    - Set cache untuk total, processed=0, failed=0
    - Loop siswa per 100 chunk → dispatch `SyncPembayaranSiswaJob` untuk setiap siswa
6. Queue worker memproses `SyncPembayaranSiswaJob`:
    - Ambil data pembayaran via API
    - Simpan ke DB column `pembayaran` (JSON)
    - Increment `sync_pembayaran_processed` counter
    - Jika gagal, increment `sync_pembayaran_failed`
7. Frontend polling `/admin/siswa/get-progress-pembayaran` setiap 500ms
8. Progress bar update secara real-time
9. Saat progress mencapai 100%, polling stop dan tampil notifikasi selesai

## Troubleshooting

### Queue jobs tidak tereksekusi

-   Pastikan queue worker sudah running
-   Check job di database table `jobs`
-   Cek failed jobs di table `failed_jobs`

### Progress tidak update

-   Pastikan cache connection bekerja
-   Run `php artisan cache:clear`

### API timeout

-   Naikkan `--timeout` value di queue worker command
-   Atau naikkan `timeout` di `SiswaService::getPembayaranSiswa()` (default 15 detik)
