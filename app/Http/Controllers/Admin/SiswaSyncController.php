<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SiswaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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


    public function getPembayaranSiswa($idperson)
    {
        $result = $this->service->getPembayaranSiswa($idperson);

        return response()->json($result);
    }

    public function syncPembayaranSiswa($id)
    {
        $result = $this->service->syncPembayaranSiswa($id);

        return response()->json($result);
    }
    public function getProgressPembayaran()
    {
        $progress = Cache::get('progress_pembayaran', 0);
        return response()->json(['progress' => $progress]);
    }

}
