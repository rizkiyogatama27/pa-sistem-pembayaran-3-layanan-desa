<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->email;
        $password = $this->password;

        try {
            $existingUser = User::where('email', $email)->first();

            if (! $existingUser) {
                RateLimiter::hit($this->throttleKey());
                throw ValidationException::withMessages([
                    'email' => 'Akun belum terdaftar. Silakan register terlebih dahulu.',
                ]);
            }

            // Try standard email/password login
            if (Auth::attempt(['email' => $email, 'password' => $password], $this->boolean('remember'))) {
                RateLimiter::clear($this->throttleKey());
                return;
            }

            // Existing account but password login failed (includes Google-only password case)
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah. Jika akun dibuat dari Google, silakan set password terlebih dahulu.',
            ]);
        } catch (\PDOException $e) {
            // Database driver error - log and show helpful message
            \Illuminate\Support\Facades\Log::error('Database driver error during login: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'email' => 'Terjadi kesalahan koneksi database. Hubungi administrator untuk bantuan.',
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Other database or query errors
            \Illuminate\Support\Facades\Log::error('Login database error: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'email' => 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.',
            ]);
        }
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
