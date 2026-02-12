<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Services\SiswaService;
use App\Models\User;
use App\Models\Siswa;
use App\Models\PetugasSiswa;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $lembagaUser = auth()->user()->lembaga;
        $scope = Siswa::query();

        // 1) Scope berdasarkan Role
        if (Auth::user()->hasRole('petugas')) {
            $scope->whereHas('petugas', function ($q) {
                $q->where('users.id', Auth::id());
            });
        } else {
            $scope->where(function ($q) use ($lembagaUser) {
                $q->where('UnitFormal', $lembagaUser)
                    ->orWhere('AsramaPondok', $lembagaUser)
                    ->orWhere('TingkatDiniyah', $lembagaUser);
            });
        }

        $filterOptions = [];
        // Kolom tabel siswa untuk dropdown
        $siswaCols = ['UnitFormal', 'KelasFormal', 'AsramaPondok', 'KamarPondok', 'TingkatDiniyah', 'KelasDiniyah'];

        // 2) Ambil Options (Hanya saat bukan request AJAX)
        if (!$request->ajax()) {
            foreach ($siswaCols as $col) {
                $filterOptions[$col] = (clone $scope)
                    ->select($col)
                    ->whereNotNull($col)
                    ->distinct()
                    ->orderBy($col)
                    ->pluck($col);
            }
            // Ambil Enum dari tabel 'penanganan'
            $filterOptions['status_penanganan'] = $this->getEnumValues('penanganan', 'status');
            $filterOptions['hasil_penanganan'] = $this->getEnumValues('penanganan', 'hasil');
        }

        // 3) Logika Penguncian Lembaga
        $lock = ['UnitFormal' => false, 'AsramaPondok' => false, 'TingkatDiniyah' => false];
        $selected = ['UnitFormal' => null, 'AsramaPondok' => null, 'TingkatDiniyah' => null];

        if (!$request->ajax()) {
            foreach (['UnitFormal', 'AsramaPondok', 'TingkatDiniyah'] as $f) {
                if (isset($filterOptions[$f]) && in_array($lembagaUser, $filterOptions[$f]->toArray())) {
                    $lock[$f] = true;
                    $selected[$f] = $lembagaUser;
                }
            }
        }

        // 4) Build Query Utama
        $query = (clone $scope);

        // Apply Filter Siswa (Lembaga Terkunci + Dropdown Siswa)
        foreach (array_merge($selected, $request->only($siswaCols)) as $field => $value) {
            if ($value) {
                $query->where($field, $value);
            }
        }

        // Filter Khusus Penanganan (menggunakan relasi 'penanganan' di Model Siswa)
        if ($request->status_penanganan) {
            if ($request->status_penanganan === 'belum_ditangani') {
                // Logika: Siswa yang sama sekali tidak punya record di tabel penanganan
                $query->whereDoesntHave('penanganan');
            } else {
                $query->whereHas('penanganan', function ($q) use ($request) {
                    $q->where('status', $request->status_penanganan);
                });
            }
        }

        if ($request->hasil_penanganan) {
            $query->whereHas('penanganan', function ($q) use ($request) {
                $q->where('hasil', $request->hasil_penanganan);
            });
        }

        // Search (Gunakan scopeSearch yang ada di model Siswa)
        if ($request->search) {
            $query->search($request->search);
        }

        $siswa = $query->paginate(40)->appends($request->query());

        // Response AJAX atau Full Page
        if ($request->ajax()) {
            return view('petugas.siswa.partials.list-siswa', compact('siswa'))->render();
        }

        return view('petugas.siswa.index', compact('siswa', 'filterOptions', 'lock', 'selected'));
    }

    public function show($id)
    {
        $siswa = Siswa::with([
            'pembayaran' => function ($q) {
                $q->orderBy('periode', 'desc');
            }
        ])->findOrFail($id);

        return view('petugas.siswa.show', compact('siswa'));
    }

    private function getEnumValues($table, $column)
    {
        // Hapus DB::raw, gunakan string langsung
        $results = \DB::select("SHOW COLUMNS FROM {$table} WHERE Field = ?", [$column]);

        if (empty($results))
            return [];

        $type = $results[0]->Type;

        // Mengekstrak nilai di dalam tanda petik
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $values = [];
        if (isset($matches[1])) {
            foreach (explode(',', $matches[1]) as $value) {
                $values[] = trim($value, "'");
            }
        }
        return $values;
    }
}
