<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiswaService;
use Illuminate\Http\Request;

class SiswaSyncController extends Controller
{
    protected $service;

    public function __construct(SiswaService $service)
    {
        $this->service = $service;
    }

    // public function index()
    // {
    //     return view('admin.assign.get-all-siswa');
    // }



    /**
     * Test koneksi API SISDA
     */
    public function testApi()
    {
        $result = $this->service->testConnection();
        return response()->json($result, $result['status'] ? 200 : 500);
    }

    public function syncAllSiswa()
    {
        $result = $this->service->syncAllSiswa();

        return response()->json($result);
    }


    public function getAllSiswa()
    {
        $result = $this->service->getAllSiswa();

        return response()->json($result);
    }
    public function syncPembayaranSiswa()
    {
        $result = $this->service->syncPembayaranSiswa();

        return response()->json($result);
    }
    public function getPembayaranSiswa()
    {
        $result = $this->service->getPembayaranSiswa();

        return response()->json($result);
    }

}
