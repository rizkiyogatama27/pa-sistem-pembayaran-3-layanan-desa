<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Simpan no_hp langsung ke tabel users agar warga bisa isi sendiri
        // tanpa perlu menunggu admin menghubungkan akun ke data warga
        if ($request->has('no_hp')) {
            $user->no_hp = $request->input('no_hp');
        }

        $user->save();

        // Jika sudah terhubung ke data warga, sinkronisasi juga ke tabel wargas
        if ($user->warga_id) {
            $updateData = [];
            if ($request->has('no_hp')) {
                $updateData['no_hp'] = $request->input('no_hp');
            }
            if ($request->has('alamat')) {
                $updateData['alamat'] = $request->input('alamat');
            }

            if (!empty($updateData)) {
                $user->warga()->update($updateData);
            }
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
