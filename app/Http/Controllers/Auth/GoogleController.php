<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        /** @var mixed $authGuard */
        $authGuard = Auth::guard();

        if (method_exists($authGuard, 'getRecallerName')) {
            Cookie::queue(Cookie::forget($authGuard->getRecallerName()));
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $role = $request->query('role', 'user');

        if (! in_array($role, ['admin', 'user'], true)) {
            $role = 'user';
        }

        session(['oauth_login_role' => $role]);

        Log::info('Google OAuth redirect started', [
            'role' => $role,
            'session_id' => $request->session()->getId(),
            'host' => $request->getHost(),
        ]);

        /** @var mixed $googleProvider */
        $googleProvider = Socialite::driver('google');

        return $googleProvider
            ->with([
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $targetRole = session('oauth_login_role', 'user');

        if (! in_array($targetRole, ['admin', 'user'], true)) {
            $targetRole = 'user';
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException $e) {
            Log::warning('Google OAuth invalid state, trying stateless fallback', [
                'host' => $request->getHost(),
                'session_id' => $request->session()->getId(),
                'message' => $e->getMessage(),
            ]);

            try {
                /** @var mixed $googleProvider */
                $googleProvider = Socialite::driver('google');
                $googleUser = $googleProvider->stateless()->user();
            } catch (\Throwable $fallbackError) {
                Log::error('Google OAuth stateless fallback failed', [
                    'host' => $request->getHost(),
                    'session_id' => $request->session()->getId(),
                    'message' => $fallbackError->getMessage(),
                ]);

                throw ValidationException::withMessages([
                    'email' => 'Login Google gagal. Coba lagi.',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback failed', [
                'host' => $request->getHost(),
                'session_id' => $request->session()->getId(),
                'message' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Login Google gagal. Coba lagi.',
            ]);
        }

        $email = (string) ($googleUser->getEmail() ?? '');

        if ($email === '') {
            throw ValidationException::withMessages([
                'email' => 'Akun Google tidak menyediakan email.',
            ]);
        }

        Log::info('Google OAuth callback received user', [
            'email' => $email,
            'target_role' => $targetRole,
            'host' => $request->getHost(),
        ]);

        $googleId = (string) $googleUser->getId();
        $avatar = $googleUser->getAvatar();
        $displayName = $this->resolveGoogleDisplayName($googleUser->getName(), $email);

        $user = User::where('email', $email)->first();

        if (! $user) {
            if ($targetRole === 'admin') {
                throw ValidationException::withMessages([
                    'email' => 'Akun admin harus dibuat dulu oleh sistem.',
                ]);
            }

            $user = User::create([
                'name' => $displayName,
                'email' => $email,
                'password' => Hash::make(str()->random(40)),
                'role' => 'user',
                'google_id' => $googleId,
                'google_avatar' => is_string($avatar) ? $avatar : null,
            ]);
        } else {
            if ($targetRole === 'admin' && $user->role !== 'admin') {
                throw ValidationException::withMessages([
                    'email' => 'Akun ini bukan admin.',
                ]);
            }

            if ($targetRole === 'user' && $user->role !== 'user') {
                throw ValidationException::withMessages([
                    'email' => 'Akun ini bukan user.',
                ]);
            }

            $user->name = $displayName;
            $user->google_id = $user->google_id ?: $googleId;

            if (is_string($avatar) && $avatar !== '') {
                $user->google_avatar = $avatar;
            }

            $user->save();
        }

        Auth::login($user, false);
        $request->session()->regenerate();
        session()->forget('oauth_login_role');

        if ($user->role === 'admin') {
            Log::info('Google OAuth login success', [
                'email' => $user->email,
                'role' => $user->role,
                'redirect_to' => '/admin/dashboard',
            ]);

            return redirect('/admin/dashboard');
        }

        Log::info('Google OAuth login success', [
            'email' => $user->email,
            'role' => $user->role,
            'redirect_to' => '/user/dashboard',
        ]);

        return redirect('/user/dashboard');
    }

    private function resolveGoogleDisplayName(?string $googleName, string $email): string
    {
        $candidate = trim((string) $googleName);

        if ($candidate !== '') {
            $candidate = preg_replace('/\d+/', '', $candidate) ?? '';
            $candidate = trim(preg_replace('/\s+/', ' ', $candidate) ?? '');
            $candidate = preg_replace('/[^\pL\s]/u', '', $candidate) ?? '';
            $candidate = trim(preg_replace('/\s+/', ' ', $candidate) ?? '');

            if ($candidate !== '') {
                return $candidate;
            }
        }

        $emailLocalPart = strstr($email, '@', true);
        $emailLocalPart = $emailLocalPart !== false ? $emailLocalPart : $email;
        $emailLocalPart = preg_replace('/[\d._-]+/', ' ', $emailLocalPart) ?? '';
        $emailLocalPart = preg_replace('/[^\pL\s]/u', '', $emailLocalPart) ?? '';
        $emailLocalPart = trim(preg_replace('/\s+/', ' ', $emailLocalPart) ?? '');

        if ($emailLocalPart !== '') {
            return $emailLocalPart;
        }

        return 'User';
    }
}
