<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Siswa;

class SiswaService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = "https://api.daruttaqwa.or.id/sisda/v1/siswa";
    }

    /**
     * Test koneksi API
     */
    public function testConnection()
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Koneksi API berhasil.',
                    'http_code' => $response->status()
                ];
            }

            return [
                'status' => false,
                'message' => 'Koneksi gagal: ' . $response->body(),
                'http_code' => $response->status()
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
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

            // Batch insert menggunakan chunk agar tidak overload
            foreach (array_chunk($insertData, 500) as $chunk) {
                \DB::table('siswa')->upsert(
                    $chunk,
                    ['idperson'],  // unique key
                    [   // columns to update
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
                'message' => 'Sinkronisasi batch selesai.',
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
            // 1. Ambil semua data dari API
            $response = Http::timeout(20)->get($this->baseUrl);

            if (!$response->successful()) {
                return [
                    'status' => false,
                    'message' => 'Gagal mengambil data dari API.'
                ];
            }

            $apiData = $response->json()['data'] ?? [];

            if (empty($apiData)) {
                return [
                    'status' => false,
                    'message' => 'Data API kosong.'
                ];
            }

            // Ambil semua idperson dari API
            $apiIds = collect($apiData)->pluck('idperson')->toArray();

            // Ambil semua idperson di database
            $dbIds = Siswa::pluck('idperson')->toArray();

            // Tentukan mana yang update/insert/delete
            $toInsertOrUpdate = [];
            $toDeleteIds = array_diff($dbIds, $apiIds);

            foreach ($apiData as $item) {
                $toInsertOrUpdate[] = [
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

            // Hitung insert & update
            $insertCount = 0;
            $updateCount = 0;

            foreach ($toInsertOrUpdate as $row) {
                if (in_array($row['idperson'], $dbIds)) {
                    $updateCount++;
                } else {
                    $insertCount++;
                }
            }

            // 2. Upsert (insert/update)
            foreach (array_chunk($toInsertOrUpdate, 500) as $chunk) {
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

            // 3. Delete siswa yang tidak ada di API
            $deleteCount = 0;
            if (!empty($toDeleteIds)) {
                $deleteCount = Siswa::whereIn('idperson', $toDeleteIds)->delete();
            }

            return [
                'status' => true,
                'message' => 'Sinkronisasi selesai.',
                'inserted' => $insertCount,
                'updated' => $updateCount,
                'deleted' => $deleteCount,
                'total_api' => count($apiData),
                'total_local' => Siswa::count()
            ];

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


}
