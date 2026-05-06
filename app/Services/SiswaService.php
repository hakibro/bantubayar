<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SiswaService
{
    public function updateTelepon(string $idperson, string $pemilik, string $nomor): array
    {
        $response = Http::withHeaders([
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ])->withCookies([
            'SWN' => env('API_SWN'),
        ], 'api.daruttaqwa.or.id')
            ->post(
                'https://api.daruttaqwa.or.id/sisda/v1/update_telepon/' . $idperson,
                ['pemilik' => $pemilik, 'nomor' => $nomor]
            );

        if (!$response->successful()) {
            throw new \Exception('Gagal update nomor telepon: ' . $response->body());
        }

        return ['status' => true, 'data' => $response->json()];
    }
}
