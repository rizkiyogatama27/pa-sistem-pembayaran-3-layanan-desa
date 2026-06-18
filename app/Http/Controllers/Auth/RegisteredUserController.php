<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\AutoGenerateTagihanService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nik' => ['required', 'digits:16', 'regex:/^[0-9]{16}$/'],
            'kk' => ['required', 'digits:16', 'regex:/^[0-9]{16}$/'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nik' => $request->nik,
            'kk' => $request->kk,
            'role' => 'user',
            'verification_status' => 'pending',
        ]);

        event(new Registered($user));

        // Generate monthly tagihan automatically if possible
        try {
            app(AutoGenerateTagihanService::class)->generateForUser($user);
        } catch (\Throwable $e) {
            // silent: do not block registration on invoice generation failure
        }

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
