<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiswaService
{
    public function updateTelepon(string $idperson, string $pemilik, string $nomor): void
    {
        $phone = DB::table('daruttaqwa_person.tbl_person_phone')
            ->where('idperson', $idperson)
            ->where('pemilik', $pemilik);

        if ($phone->exists()) {
            $phone->update(['phone_number' => $nomor]);

            return;
        }

        DB::table('daruttaqwa_person.tbl_person_phone')->insert([
            'phoneid' => time().'-'.strtoupper(Str::random(6)),
            'idperson' => $idperson,
            'pemilik' => $pemilik,
            'phone_number' => $nomor,
            'status' => 1,
            'penerima' => 1,
        ]);
    }
}
