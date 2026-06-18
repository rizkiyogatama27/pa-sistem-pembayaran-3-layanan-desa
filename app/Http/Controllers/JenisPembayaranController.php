<?php

namespace App\Http\Controllers;

use App\Models\JenisPembayaran;
use Illuminate\Http\Request;

class JenisPembayaranController extends Controller
{
    public function index()
    {
        $jenisPembayarans = JenisPembayaran::query()
            ->orderBy('nama')
            ->get();

        return view('jenis_pembayaran.index', compact('jenisPembayarans'));
    }

    public function create()
    {
        return view('jenis_pembayaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        JenisPembayaran::create($validated);

        return redirect()->route('jenis-pembayaran.index')
            ->with('success', 'Data berhasil ditambahkan');
    }

    public function edit(JenisPembayaran $jenis_pembayaran)
    {
        return view('jenis_pembayaran.edit', compact('jenis_pembayaran'));
    }

    public function update(Request $request, JenisPembayaran $jenis_pembayaran)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $jenis_pembayaran->update($validated);

        return redirect()->route('jenis-pembayaran.index')
            ->with('success', 'Data berhasil diupdate');
    }

    public function destroy(JenisPembayaran $jenis_pembayaran)
    {
        $jenis_pembayaran->delete();
        return redirect()->route('jenis-pembayaran.index')
            ->with('success', 'Data berhasil dihapus');
    }
}