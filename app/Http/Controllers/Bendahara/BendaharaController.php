<?php

namespace App\Http\Controllers\Bendahara;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Siswa;
use Illuminate\Http\Request;

class BendaharaController extends Controller
{
    public function index(Request $request)
    {
        $lembagaUser = auth()->user()->lembaga;

        // ================================================
        // 1) Tentukan scope siswa sesuai lembaga user
        // ================================================
        $scope = Siswa::query()
            ->where(function ($q) use ($lembagaUser) {
                $q->where('UnitFormal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatDiniyah', $lembagaUser);
            });


        // ================================================
        // 2) Filter dropdown (hanya data dalam scope)
        // ================================================
        $columns = [
            'UnitFormal',
            'KelasFormal',
            'AsramaPondok',
            'KamarPondok',
            'TingkatDiniyah',
            'KelasDiniyah',
        ];

        $filterOptions = [];

        foreach ($columns as $col) {
            $filterOptions[$col] = (clone $scope)
                ->select($col)
                ->whereNotNull($col)
                ->distinct()
                ->orderBy($col)
                ->pluck($col);
        }


        // ================================================
        // 3) Tentukan lembaga mana yang terkunci otomatis
        // ================================================
        $lock = [
            'UnitFormal' => false,
            'AsramaPondok' => false,
            'TingkatDiniyah' => false,
        ];

        $selected = [
            'UnitFormal' => null,
            'AsramaPondok' => null,
            'TingkatDiniyah' => null,
        ];

        if (in_array($lembagaUser, $filterOptions['UnitFormal']->toArray())) {
            $lock['UnitFormal'] = true;
            $selected['UnitFormal'] = $lembagaUser;
        }

        if (in_array($lembagaUser, $filterOptions['AsramaPondok']->toArray())) {
            $lock['AsramaPondok'] = true;
            $selected['AsramaPondok'] = $lembagaUser;
        }

        if (in_array($lembagaUser, $filterOptions['TingkatDiniyah']->toArray())) {
            $lock['TingkatDiniyah'] = true;
            $selected['TingkatDiniyah'] = $lembagaUser;
        }

        // ================================================
        // 4) Query siswa
        // ================================================
        $query = (clone $scope);

        // Filter berdasarkan lembaga yang terkunci
        foreach ($selected as $field => $value) {
            if ($value) {
                $query->where($field, $value);
            }
        }

        // Filter manual dari dropdown user
        foreach ($request->only([
            'UnitFormal',
            'KelasFormal',
            'AsramaPondok',
            'KamarPondok',
            'TingkatDiniyah',
            'KelasDiniyah'
        ]) as $field => $value) {
            if ($value) {
                $query->where($field, $value);
            }
        }

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('idperson', 'like', "%{$request->search}%");
            });
        }

        $siswa = $query->paginate(40)->appends($request->query());

        return view('bendahara.siswa.index', compact(
            'siswa',
            'filterOptions',
            'lock',
            'selected'
        ));
    }





    public function show($id)
    {
        $siswa = Siswa::with([
            'pembayaran' => function ($q) {
                $q->orderBy('periode', 'desc');
            }
        ])->findOrFail($id);

        return view('bendahara.siswa.show', compact('siswa'));
    }
}
