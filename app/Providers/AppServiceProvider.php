<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $host = request()->getHost();

        // Ngrok and Vercel serve pages over HTTPS and forward to local HTTP.
        // Force HTTPS URL generation to avoid mixed-content blocked CSS/JS and insecure form submission warnings.
        if (app()->environment('production') || Str::contains($host, 'ngrok-free.dev') || Str::contains($host, 'ngrok.app') || Str::contains($host, 'vercel.app')) {
            URL::forceScheme('https');
        }

        $branding = [
            'app_name' => config('app.name', 'Laravel'),
            'village_name' => 'Portal Desa',
            'tagline' => 'Sistem Pembayaran Layanan Desa',
            'logo_url' => null,
            'contact_phone' => null,
            'contact_email' => null,
            'address' => null,
        ];

        try {
            $settings = SystemSetting::query()
                ->whereIn('key', array_keys($branding))
                ->pluck('value', 'key')
                ->toArray();

            foreach ($branding as $key => $defaultValue) {
                if (array_key_exists($key, $settings) && $settings[$key] !== null && $settings[$key] !== '') {
                    $branding[$key] = $settings[$key];
                }
            }
        } catch (\Throwable $e) {
            // Tables may not exist yet during first migration/setup.
        }

        View::share('branding', $branding);
    }
}
