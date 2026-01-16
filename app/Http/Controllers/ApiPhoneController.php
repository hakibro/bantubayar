<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class ApiPhoneController extends Controller
{
    public function update()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->withCookies([
                'SWN' => env('API_SWN'), // 👈 COOKIE DIPAKSA DI SINI
            ], 'api.daruttaqwa.or.id')
            ->post(
                'https://api.daruttaqwa.or.id/sisda/v1/update_telepon/20181222',
                [
                    'pemilik' => 'ayah',
                    'nomor' => '088888888888',
                ]
            );

        return response()->json([
            'status' => $response->successful(),
            'data' => $response->json(),
        ], $response->status());
    }
}
