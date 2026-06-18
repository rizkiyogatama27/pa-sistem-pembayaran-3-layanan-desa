<?php

namespace App\Http\Controllers;

use App\Models\Keluarga;
use App\Support\AdminActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KeluargaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $keluargas = Keluarga::query()
            ->withCount('wargas')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('no_kk', 'like', "%{$search}%")
                    ->orWhere('nama_keluarga', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            })
            ->orderBy('nama_keluarga')
            ->paginate(10)
            ->withQueryString();

        return view('keluarga.index', compact('keluargas', 'search'));
    }

    public function create(): View
    {
        return view('keluarga.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'no_kk' => ['required', 'string', 'max:32', Rule::unique('keluargas', 'no_kk')],
            'nama_keluarga' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
        ], [
            'no_kk.unique' => 'No. KK sudah terdaftar. Gunakan No. KK lain.',
        ]);

        $keluarga = Keluarga::create($validated);

        AdminActivity::log('keluarga', 'create', 'Menambahkan data keluarga baru.', [
            'keluarga_id' => $keluarga->id,
            'no_kk' => $keluarga->no_kk,
        ]);

        return redirect()->route('keluarga.index')
            ->with('success', 'Data keluarga berhasil ditambahkan.');
    }

    public function edit(Keluarga $keluarga): View
    {
        return view('keluarga.edit', compact('keluarga'));
    }

    public function update(Request $request, Keluarga $keluarga): RedirectResponse
    {
        $validated = $request->validate([
            'no_kk' => ['required', 'string', 'max:32', Rule::unique('keluargas', 'no_kk')->ignore($keluarga->id)],
            'nama_keluarga' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
        ], [
            'no_kk.unique' => 'No. KK sudah terdaftar. Gunakan No. KK lain.',
        ]);

        $keluarga->update($validated);

        AdminActivity::log('keluarga', 'update', 'Memperbarui data keluarga.', [
            'keluarga_id' => $keluarga->id,
            'no_kk' => $keluarga->no_kk,
        ]);

        return redirect()->route('keluarga.index')
            ->with('success', 'Data keluarga berhasil diperbarui.');
    }

    public function destroy(Keluarga $keluarga): RedirectResponse
    {
        $anggotaWarga = $keluarga->wargas()->count();
        $anggotaUser = $keluarga->users()->count();

        if ($anggotaWarga > 0 || $anggotaUser > 0) {
            return back()->with('error', 'Keluarga tidak bisa dihapus karena masih dipakai oleh data warga/user.');
        }

        $deletedId = $keluarga->id;
        $deletedNoKk = $keluarga->no_kk;

        $keluarga->delete();

        AdminActivity::log('keluarga', 'delete', 'Menghapus data keluarga.', [
            'keluarga_id' => $deletedId,
            'no_kk' => $deletedNoKk,
        ]);

        return redirect()->route('keluarga.index')
            ->with('success', 'Data keluarga berhasil dihapus.');
    }
}
