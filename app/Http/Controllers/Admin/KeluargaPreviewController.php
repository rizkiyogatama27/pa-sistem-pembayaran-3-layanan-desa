<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KeluargaPreviewController extends Controller
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

        return view('admin.keluarga-preview', compact('keluargas', 'search'));
    }
}
