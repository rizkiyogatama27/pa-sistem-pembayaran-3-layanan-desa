<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Support\AdminActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function edit(): View
    {
        $settings = [
            'app_name' => SystemSetting::getValue('app_name', config('app.name')),
            'village_name' => SystemSetting::getValue('village_name', 'Portal Desa'),
            'tagline' => SystemSetting::getValue('tagline', 'Sistem Pembayaran Layanan Desa'),
            'logo_url' => SystemSetting::getValue('logo_url', ''),
            'contact_phone' => SystemSetting::getValue('contact_phone', ''),
            'contact_email' => SystemSetting::getValue('contact_email', ''),
            'address' => SystemSetting::getValue('address', ''),
        ];

        return view('admin.settings.branding', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:120'],
            'village_name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:180'],
            'logo_url' => ['nullable', 'url', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::setValue($key, $value);
        }

        AdminActivity::log('settings', 'update_branding', 'Memperbarui pengaturan branding sistem.', [
            'keys' => array_keys($validated),
        ]);

        return redirect()->route('admin.settings.branding.edit')
            ->with('success', 'Pengaturan branding berhasil disimpan.');
    }
}
