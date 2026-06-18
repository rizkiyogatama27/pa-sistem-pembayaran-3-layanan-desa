<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserSettingsController extends Controller
{
    /**
     * Show the set password form for users who logged in via Google.
     */
    public function showSetPassword(Request $request): \Illuminate\View\View|RedirectResponse
    {
        $email = $request->query('email');

        if (!$email) {
            abort(400, 'Email parameter required');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            abort(404, 'User tidak ditemukan');
        }

        // If already logged in and has password, redirect to dashboard
        if (Auth::check() && Auth::id() === $user->id && $user->password_set_at) {
            return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard');
        }

        return view('user-settings.set-password', compact('user', 'email'));
    }

    /**
     * Store password for user who logged in via Google.
     */
    public function storePassword(Request $request): RedirectResponse
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($validated['password']),
                'password_set_at' => now(),
            ]);

        // Auto-login setelah set password
        Auth::login($user, true);

        return redirect()->route($user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard')
            ->with('success', 'Password berhasil diatur. Selamat datang!');
    }

    /**
     * Show change password form (untuk user yang sudah set password).
     */
    public function showChangePassword(): \Illuminate\View\View
    {
        return view('user-settings.change-password');
    }

    /**
     * Update password yang sudah tersetting.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password saat ini tidak cocok.',
            ]);
        }

        // Update password
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'password' => Hash::make($validated['password']),
            ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
