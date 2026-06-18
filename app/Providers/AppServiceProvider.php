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

        // Ngrok serves pages over HTTPS and forwards to local HTTP.
        // Force HTTPS URL generation to avoid mixed-content blocked CSS/JS on mobile.
        if (Str::contains($host, 'ngrok-free.dev') || Str::contains($host, 'ngrok.app')) {
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
