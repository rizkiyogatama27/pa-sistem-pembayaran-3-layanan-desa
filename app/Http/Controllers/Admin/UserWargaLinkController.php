<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\User;
use App\Models\Warga;
use App\Support\AdminActivity;
use App\Services\AutoGenerateTagihanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserWargaLinkController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->where('role', 'user')
            ->with('warga')
            ->orderBy('name')
            ->get();

        $wargas = Warga::query()->orderBy('nama')->get();

        $users = $users->map(function (User $user) use ($wargas) {
            $user->matching_wargas = $this->resolveMatchingWargas($user, $wargas);
            $user->suggested_warga_id = $user->matching_wargas->first()->id ?? null;

            // Jika tidak ada kecocokan, tampilkan semua warga yang belum terhubung
            if ($user->matching_wargas->isEmpty()) {
                $user->available_wargas = collect($wargas)->filter(function ($warga) {
                    return ! User::query()->where('warga_id', $warga->id)->exists();
                })->values();
            } else {
                $user->available_wargas = $user->matching_wargas;
            }

            return $user;
        });

        return view('admin.user-warga', compact('users', 'wargas'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', 'Akun ini bukan akun dengan role user.');
        }

        $validated = $request->validate([
            'verification_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'warga_id' => [
                'nullable',
                'exists:wargas,id',
                Rule::unique('users', 'warga_id')->ignore($user->id),
            ],
        ], [
            'warga_id.unique' => 'Data warga ini sudah terhubung ke akun user lain.',
        ]);

        $user->verification_status = $validated['verification_status'];
        $user->warga_id = null;
        $user->keluarga_id = null;

        if ($validated['verification_status'] === 'approved') {
            if (! empty($validated['warga_id'])) {
                $user->warga_id = $validated['warga_id'];
                $user->keluarga_id = Warga::query()->where('id', $user->warga_id)->value('keluarga_id');
            } else {
                // Jika admin menyetujui tanpa memilih warga, coba hubungkan otomatis:
                // 1) jika ada warga dengan NIK yang sama, gunakan itu
                if (! $user->warga_id && ! empty($user->nik)) {
                    $existing = Warga::query()->where('nik', $user->nik)->first();
                    if ($existing) {
                        $user->warga_id = $existing->id;
                        $user->keluarga_id = $existing->keluarga_id;
                    }
                }

                // 2) jika belum terhubung tapi ada KK, coba kaitkan ke keluarga yang ada
                if (! $user->warga_id && ! empty($user->kk)) {
                    $kel = Keluarga::query()->where('no_kk', $user->kk)->first();
                    if ($kel) {
                        // buat warga baru di keluarga tersebut jika belum ada dengan NIK yang sama
                        $warga = null;
                        if (! empty($user->nik)) {
                            $warga = Warga::query()->where('nik', $user->nik)->first();
                        }

                        if (! $warga) {
                            $warga = Warga::query()->create([
                                'nik' => $user->nik ?? null,
                                'nama' => $user->name ?? ('Warga ' . $user->id),
                                'alamat' => '-',
                                'keluarga_id' => $kel->id,
                            ]);
                        }

                        if ($warga) {
                            $user->warga_id = $warga->id;
                            $user->keluarga_id = $warga->keluarga_id;
                        }
                    }
                }

                // 3) jika masih belum terhubung, buat keluarga & warga baru otomatis
                if (! $user->warga_id) {
                    // buat nilai no_kk yang unik jika tidak ada
                    $noKk = $user->kk ?: ('AUTO-' . now()->timestamp . '-' . $user->id);

                    try {
                        $newKeluarga = Keluarga::query()->firstOrCreate([
                            'no_kk' => $noKk,
                        ], [
                            'nama_keluarga' => $user->name ?? ('Keluarga ' . $user->id),
                            'alamat' => '-',
                        ]);

                        $newWarga = Warga::query()->create([
                            'nik' => $user->nik ?? null,
                            'nama' => $user->name ?? ('Warga ' . $user->id),
                            'alamat' => '-',
                            'keluarga_id' => $newKeluarga->id,
                        ]);

                        if ($newWarga) {
                            $user->warga_id = $newWarga->id;
                            $user->keluarga_id = $newWarga->keluarga_id;
                        }
                    } catch (\Throwable $e) {
                        // kalau gagal membuat, biarkan user disetujui tanpa mapping
                    }
                }
            }
        }

        $user->save();

        AdminActivity::log('user-warga', 'update_mapping', 'Memperbarui relasi akun user ke warga.', [
            'user_id' => $user->id,
            'warga_id' => $user->warga_id,
            'keluarga_id' => $user->keluarga_id,
            'verification_status' => $user->verification_status,
        ]);

        // Jika disetujui, coba generate tagihan otomatis untuk user ini.
        $successMessage = 'Status verifikasi akun user berhasil diperbarui.';
        if ($user->verification_status === 'approved') {
            if ($user->warga_id) {
                try {
                    $created = app(AutoGenerateTagihanService::class)->generateForUser($user);
                    if (is_int($created) && $created > 0) {
                        $successMessage .= ' Dibuat ' . $created . ' tagihan otomatis.';
                    }
                } catch (\Throwable $e) {
                    // Jangan ganggu alur admin jika gagal membuat tagihan otomatis
                }
            } else {
                $successMessage .= ' User disetujui tanpa data warga dan bisa dipetakan nanti.';
            }
        }

        return back()->with('success', $successMessage);
    }

    private function resolveMatchingWargas(User $user, iterable $wargas)
    {
        $wargaCollection = collect($wargas);
        $matches = collect();
        $userNik = trim((string) ($user->nik ?? ''));

        if ($userNik !== '') {
            foreach ($wargaCollection as $warga) {
                if (trim((string) ($warga->nik ?? '')) === $userNik) {
                    $matches->push($warga);
                }
            }

            if ($matches->isNotEmpty()) {
                // Hapus warga yang sudah terhubung ke akun user lain
                $filtered = $matches->filter(function ($warga) use ($user) {
                    return ! User::query()->where('warga_id', $warga->id)->where('id', '!=', $user->id)->exists();
                });

                return $filtered->unique('id')->values();
            }
        }

        $userKk = trim((string) ($user->kk ?? ''));

        if ($userKk !== '') {
            $keluargaId = Keluarga::query()
                ->where('no_kk', $userKk)
                ->value('id');

            if ($keluargaId) {
                $kkMatches = Warga::query()
                    ->where('keluarga_id', $keluargaId)
                    ->orderBy('nama')
                    ->get();

                if ($kkMatches->isNotEmpty()) {
                    $filteredKk = $kkMatches->filter(function ($warga) use ($user) {
                        return ! User::query()->where('warga_id', $warga->id)->where('id', '!=', $user->id)->exists();
                    });

                    if ($filteredKk->isNotEmpty()) {
                        return $filteredKk->unique('id')->values();
                    }
                }
            }
        }

        if ($user->warga_id) {
            $currentWarga = $wargaCollection->firstWhere('id', $user->warga_id);

            if ($currentWarga) {
                $matches->push($currentWarga);
            }
        }

        // filter final matches to exclude warga yang sudah dipakai user lain
        $final = $matches->filter(function ($warga) use ($user) {
            return ! User::query()->where('warga_id', $warga->id)->where('id', '!=', $user->id)->exists();
        });

        return $final->unique('id')->values();
    }
}
