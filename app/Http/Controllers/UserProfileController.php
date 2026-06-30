<?php

namespace App\Http\Controllers;

use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $warga = null;

        if (($user?->verification_status ?? 'pending') === 'approved' && $user?->warga_id) {
            $warga = Warga::find($user->warga_id);
        }

        return view('user.profile', compact('warga', 'user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (($user?->verification_status ?? 'pending') !== 'approved' || !$user?->warga_id) {
            return back()->with('error', 'Akun Anda belum diverifikasi atau belum dihubungkan dengan data warga.');
        }

        $warga = Warga::find($user->warga_id);

        if (!$warga) {
            return back()->with('error', 'Data warga tidak ditemukan.');
        }

        $validated = $request->validate([
            'no_hp'  => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ], [
            'no_hp.max'  => 'Nomor HP maksimal 20 karakter.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
        ]);

        $warga->update([
            'no_hp'  => $validated['no_hp']  ?? $warga->no_hp,
            'alamat' => $validated['alamat'] ?? $warga->alamat,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui! Nomor HP dan alamat Anda sudah terupdate.');
    }
}
