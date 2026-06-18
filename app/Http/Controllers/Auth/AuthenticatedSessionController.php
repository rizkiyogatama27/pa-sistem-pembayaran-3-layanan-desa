<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AutoGenerateTagihanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AutoGenerateTagihanService $autoGenerateTagihanService): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if (Auth::user()?->role === 'admin') {
            return redirect('/admin/dashboard');
        }

        if (Auth::user()?->role === 'petugas') {
            return redirect('/petugas/dashboard');
        }

        if (Auth::user()) {
            try {
                $autoGenerateTagihanService->generateForUser(Auth::user());
            } catch (\Throwable $e) {
                Log::warning('Auto generate tagihan failed on login', [
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect('/user/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
