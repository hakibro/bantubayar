<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;
use App\Models\Siswa;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role(['petugas', 'bendahara'])->withTrashed();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($lembaga = $request->input('lembaga')) {
            $query->where('lembaga', $lembaga);
        }

        $petugas = $query->orderBy('name')->paginate(10)->withQueryString();

        $daftarLembaga = User::role(['petugas', 'bendahara'])
            ->select('lembaga')->distinct()->pluck('lembaga')->filter()->values();

        // Jika request dari AJAX → return partial table saja
        if ($request->ajax()) {
            return view('admin.petugas.partials.table', compact('petugas'))->render();
        }

        return view('admin.petugas.index', compact('petugas', 'daftarLembaga'));
    }

    public function create()
    {
        $lembagaRaw = Siswa::select('UnitFormal', 'AsramaPondok', 'TingkatDiniyah')->get();

        $lembaga = [
            'UnitFormal' => $lembagaRaw->pluck('UnitFormal')->filter()->unique()->sort()->values(),
            'AsramaPondok' => $lembagaRaw->pluck('AsramaPondok')->filter()->unique()->sort()->values(),
            'TingkatDiniyah' => $lembagaRaw->pluck('TingkatDiniyah')->filter()->unique()->sort()->values(),
        ];

        $roles = Role::all(); // ambil semua role

        return view('admin.petugas.create', compact('lembaga', 'roles'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'lembaga' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'lembaga' => $request->lembaga,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.petugas.index')->with('success', 'Petugas created successfully.');
    }

    public function edit($id)
    {
        $lembagaRaw = Siswa::select('UnitFormal', 'AsramaPondok', 'TingkatDiniyah')->get();

        $lembaga = [
            'UnitFormal' => $lembagaRaw->pluck('UnitFormal')->filter()->unique()->sort()->values(),
            'AsramaPondok' => $lembagaRaw->pluck('AsramaPondok')->filter()->unique()->sort()->values(),
            'TingkatDiniyah' => $lembagaRaw->pluck('TingkatDiniyah')->filter()->unique()->sort()->values(),
        ];
        $roles = Role::all(); // semua role
        $petugas = User::findOrFail($id);
        return view('admin.petugas.edit', compact('petugas', 'lembaga', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $petugas = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $petugas->id,
            'password' => 'nullable|string|min:8|confirmed',
            'lembaga' => 'nullable|string|max:255',
        ]);

        $petugas->name = $request->name;
        $petugas->email = $request->email;
        if ($request->filled('password')) {
            $petugas->password = bcrypt($request->password);
        }
        $petugas->lembaga = $request->lembaga;
        $petugas->save();

        return redirect()->route('admin.petugas.index')->with('success', 'Petugas updated successfully.');
    }

    public function destroy($id)
    {
        $petugas = User::findOrFail($id);
        $petugas->delete();

        return redirect()->route('admin.petugas.index')->with('success', 'Petugas deleted successfully.');
    }

    public function restore($id)
    {
        $petugas = User::withTrashed()->findOrFail($id);
        $petugas->restore();

        return redirect()->route('admin.petugas.index')->with('success', 'Petugas restored successfully.');
    }

    public function forceDelete($id)
    {
        $petugas = User::withTrashed()->findOrFail($id);
        $petugas->forceDelete();

        return redirect()->route('admin.petugas.index')->with('success', 'Petugas permanently deleted successfully.');
    }
}
