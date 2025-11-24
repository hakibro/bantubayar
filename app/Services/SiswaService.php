<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Siswa;

class SiswaService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = "https://api.daruttaqwa.or.id/sisda/v1/siswa";
        $this->paymentUrl = "https://api.daruttaqwa.or.id/sisda/v1/payments/";
    }

    /**
     * Test koneksi API
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl);

            if ($response->successful()) {
                $json = $response->json();
                $dataCount = isset($json['data']) && is_array($json['data'])
                    ? count($json['data'])
                    : null;

                return [
                    'status' => true,
                    'message' => 'Koneksi API berhasil.',
                    'http_code' => $response->status(),
                    'total' => $dataCount,
                    'raw' => $json
                ];
            }

            return [
                'status' => false,
                'message' => 'Koneksi gagal. Server merespon dengan error.',
                'http_code' => $response->status(),
                'error_body' => $response->body()
            ];

        } catch (\Exception $e) {

            return [
                'status' => false,
                'message' => 'Terjadi exception saat menghubungi API.',
                'error' => $e->getMessage(),
                'http_code' => 500
            ];
        }
    }



    /**
     * Ambil data siswa dari API berdasarkan idperson
     */

    public function getAllSiswa()
    {
        try {
            $response = Http::timeout(20)->get($this->baseUrl);

            if (!$response->successful()) {
                return [
                    'status' => false,
                    'message' => 'Gagal mengambil data dari API.',
                ];
            }

            $json = $response->json();
            $data = $json['data'] ?? [];

            if (empty($data)) {
                return [
                    'status' => false,
                    'message' => 'Data kosong dari API.',
                ];
            }

            $insertData = [];
            foreach ($data as $item) {
                $insertData[] = [
                    'idperson' => $item['idperson'],
                    'nama' => $item['nama'],
                    'gender' => $item['gender'],
                    'lahirtempat' => $item['lahirtempat'],
                    'lahirtanggal' => $item['lahirtanggal'],
                    'phone' => $item['phone'],
                    'UnitFormal' => $item['UnitFormal'],
                    'KelasFormal' => $item['KelasFormal'],
                    'AsramaPondok' => $item['AsramaPondok'],
                    'KamarPondok' => $item['KamarPondok'],
                    'TingkatDiniyah' => $item['TingkatDiniyah'],
                    'KelasDiniyah' => $item['KelasDiniyah'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert per chunk tanpa progress
            foreach (array_chunk($insertData, 500) as $chunk) {
                \DB::table('siswa')->upsert(
                    $chunk,
                    ['idperson'],
                    [
                        'nama',
                        'gender',
                        'lahirtempat',
                        'lahirtanggal',
                        'phone',
                        'UnitFormal',
                        'KelasFormal',
                        'AsramaPondok',
                        'KamarPondok',
                        'TingkatDiniyah',
                        'KelasDiniyah',
                        'updated_at'
                    ]
                );
            }

            return [
                'status' => true,
                'message' => 'Sinkronisasi selesai.',
                'total' => count($data),
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function syncAllSiswa()
    {
        try {
            // 1. Ambil data API
            $response = Http::timeout(20)->get($this->baseUrl);

            if (!$response->successful()) {
                return ['status' => false, 'message' => 'Gagal mengambil data dari API.'];
            }

            $apiData = $response->json()['data'] ?? [];

            if (empty($apiData)) {
                return ['status' => false, 'message' => 'Data API kosong.'];
            }

            // 2. Ambil semua data siswa local dalam bentuk map
            $dbData = Siswa::get()->keyBy('idperson');

            $fields = [
                'nama',
                'gender',
                'lahirtempat',
                'lahirtanggal',
                'phone',
                'UnitFormal',
                'KelasFormal',
                'AsramaPondok',
                'KamarPondok',
                'TingkatDiniyah',
                'KelasDiniyah',
            ];

            $insertBatch = [];
            $updateBatch = [];
            $updatedCount = 0;
            $insertCount = 0;
            $apiIds = [];

            // 3. Loop data API → tentukan insert/update
            foreach ($apiData as $item) {

                $id = $item['idperson'];
                $apiIds[] = $id;

                $local = $dbData->get($id);

                // -------------- INSERT --------------
                if (!$local) {
                    $insertBatch[] = [
                        'idperson' => $id,
                        'nama' => $item['nama'],
                        'gender' => $item['gender'],
                        'lahirtempat' => $item['lahirtanggal'],
                        'lahirtanggal' => $item['lahirtanggal'],
                        'phone' => $item['phone'],
                        'UnitFormal' => $item['UnitFormal'],
                        'KelasFormal' => $item['KelasFormal'],
                        'AsramaPondok' => $item['AsramaPondok'],
                        'KamarPondok' => $item['KamarPondok'],
                        'TingkatDiniyah' => $item['TingkatDiniyah'],
                        'KelasDiniyah' => $item['KelasDiniyah'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $insertCount++;
                    continue;
                }

                // -------------- UPDATE --------------
                $changes = false;

                foreach ($fields as $f) {

                    $localValue = $local->$f;
                    $apiValue = $item[$f];

                    // 🔥 NORMALISASI tanggal
                    if ($f === 'lahirtanggal') {
                        $localValue = optional($localValue)->format('Y-m-d');
                    }

                    if ((string) $localValue !== (string) $apiValue) {
                        $changes = true;
                        break;
                    }
                }

                if ($changes) {
                    $updateBatch[] = [
                        'idperson' => $id,
                        'nama' => $item['nama'],
                        'gender' => $item['gender'],
                        'lahirtempat' => $item['lahirtanggal'],
                        'lahirtanggal' => $item['lahirtanggal'],
                        'phone' => $item['phone'],
                        'UnitFormal' => $item['UnitFormal'],
                        'KelasFormal' => $item['KelasFormal'],
                        'AsramaPondok' => $item['AsramaPondok'],
                        'KamarPondok' => $item['KamarPondok'],
                        'TingkatDiniyah' => $item['TingkatDiniyah'],
                        'KelasDiniyah' => $item['KelasDiniyah'],
                        'updated_at' => now(),
                    ];

                    $updatedCount++;
                }
            }

            // 4. Bulk Insert
            if (!empty($insertBatch)) {
                foreach (array_chunk($insertBatch, 500) as $chunk) {
                    Siswa::insert($chunk);
                }
            }

            // 5. Bulk Update
            if (!empty($updateBatch)) {
                foreach (array_chunk($updateBatch, 500) as $chunk) {
                    \DB::table('siswa')->upsert(
                        $chunk,
                        ['idperson'],
                        $fields
                    );
                }
            }

            // 6. Delete siswa yang tidak ada di API
            $deleteCount = Siswa::whereNotIn('idperson', $apiIds)->delete();

            // 7. Hitung skipped
            $totalApi = count($apiData);
            $skipped = $totalApi - $insertCount - $updatedCount;

            return [
                'status' => true,
                'message' => 'Sinkronisasi selesai.',
                'inserted' => $insertCount,
                'updated' => $updatedCount,
                'skipped' => $skipped,
                'deleted' => $deleteCount,
                'total_api' => $totalApi,
                'total_local' => Siswa::count()
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getPembayaranSiswa($idperson)
    {
        $url = $this->paymentUrl . $idperson;

        try {
            $response = Http::timeout(15)->get($url);

            if (!$response->successful()) {
                return [
                    'status' => false,
                    'message' => 'Gagal mengambil data pembayaran.',
                    'http_code' => $response->status()
                ];
            }

            return [
                'status' => true,
                'data' => $response->json()['data'] ?? [],
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

}
