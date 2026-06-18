<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerifikasiUserController extends Controller
{
    public function index()
    {
        $users = User::where('status_verifikasi', 'pending')->get();
        return view('admin.verifikasi-user.index', compact('users'));
    }

    public function verifikasi($id)
    {
        $user = User::findOrFail($id);
        $user->status_verifikasi = 'verified';

        // Otomatis hubungkan ke warga jika ada NIK yang sama
        $wargaLinked = false;
        if ($user->nik) {
            $warga = \App\Models\Warga::where('nik', $user->nik)->first();
            if (! $warga) {
                // Buat keluarga baru
                    $keluarga = \App\Models\Keluarga::create([
                        'no_kk' => $user->kk ?? '',
                        'nama_keluarga' => $user->name ?? 'Keluarga Baru',
                        'alamat' => '-',
                    ]);
                // Buat data warga baru dengan keluarga_id
                $warga = \App\Models\Warga::create([
                    'nik' => $user->nik,
                    'nama' => $user->name,
                    'alamat' => '-',
                    'keluarga_id' => $keluarga->id,
                ]);
            }
            if ($warga) {
                $user->warga_id = $warga->id;
                $user->keluarga_id = $warga->keluarga_id;
                $wargaLinked = true;
            }
        }

        $user->save();

        // Generate tagihan otomatis jika user sudah terhubung ke warga
        if ($user->warga_id) {
            (new \App\Services\AutoGenerateTagihanService())->generateForUser($user);
        }

        if (!$wargaLinked) {
            return redirect()->route('admin.verifikasi-user.index')->with('error', 'User berhasil diverifikasi, tetapi gagal menghubungkan ke data warga.');
        }

        return redirect()->route('admin.verifikasi-user.index')->with('success', 'User berhasil diverifikasi, dihubungkan ke data warga, dan tagihan otomatis dibuat.');
    }

    public function tolak($id)
    {
        $user = User::findOrFail($id);
        $user->status_verifikasi = 'rejected';
        $user->save();
        return redirect()->route('admin.verifikasi-user.index')->with('success', 'User berhasil ditolak.');
    }
}
