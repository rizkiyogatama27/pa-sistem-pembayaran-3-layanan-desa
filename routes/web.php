<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\UserWargaLinkController;
use App\Http\Controllers\Admin\AdminActivityController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\KeluargaController;
use App\Http\Controllers\EventDonasiController;
use App\Http\Controllers\EventDonasiKontribusiController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserEventDonasiController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\Meter\SelfReportMeterController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\JenisPembayaranController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\WargaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VerifikasiUserController;

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.dashboard');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/user-warga', [UserWargaLinkController::class, 'index'])->name('admin.user-warga.index');
    Route::patch('/admin/user-warga/{user}', [UserWargaLinkController::class, 'update'])->name('admin.user-warga.update');
    Route::get('/pembayaran-wajib', [PembayaranController::class, 'wajibIndex'])->name('pembayaran.wajib');
    Route::get('/donasi', [PembayaranController::class, 'donasiIndex'])->name('donasi.index');
    Route::get('/event-donasi/laporan', [EventDonasiController::class, 'laporan'])->name('event-donasi.laporan');
    Route::resource('event-donasi', EventDonasiController::class)->except(['show']);
    Route::get('/event-donasi/{eventDonasi}/kontribusi', [EventDonasiKontribusiController::class, 'index'])->name('event-donasi.kontribusi.index');
    Route::post('/event-donasi/{eventDonasi}/kontribusi', [EventDonasiKontribusiController::class, 'store'])->name('event-donasi.kontribusi.store');
    Route::post('/event-donasi/{eventDonasi}/kontribusi/{kontribusi}/verify', [EventDonasiKontribusiController::class, 'verify'])->name('event-donasi.kontribusi.verify');
    Route::post('/event-donasi/{eventDonasi}/kontribusi/{kontribusi}/unverify', [EventDonasiKontribusiController::class, 'unverify'])->name('event-donasi.kontribusi.unverify');
    Route::delete('/event-donasi/{eventDonasi}/kontribusi/{kontribusi}', [EventDonasiKontribusiController::class, 'destroy'])->name('event-donasi.kontribusi.destroy');
    Route::resource('keluarga', KeluargaController::class)->except(['show']);
    Route::resource('warga', WargaController::class)->except(['show']);
    Route::resource('jenis-pembayaran', JenisPembayaranController::class)->except(['show']);
    Route::resource('pembayaran', PembayaranController::class)->except(['show']);
        // Route POST khusus update agar XAMPP/Windows tidak gagal spoofing PUT
        Route::post('/pembayaran/{id}/update', [PembayaranController::class, 'update'])->name('pembayaran.update.post');
    Route::get('/pembayaran/{id}/bayar-tunai', [PembayaranController::class, 'cashForm'])->name('pembayaran.cash.form');
    Route::post('/pembayaran/{id}/bayar-tunai', [PembayaranController::class, 'payCash'])->name('pembayaran.cash.pay');
    Route::post('/pembayaran/{id}/reminder-whatsapp', [PembayaranController::class, 'sendWhatsappReminder'])->name('pembayaran.reminder-whatsapp');
    Route::post('/pembayaran/reminder-whatsapp/warga/{wargaId}', [PembayaranController::class, 'sendWhatsappReminderPerWarga'])->name('pembayaran.reminder-whatsapp.warga');
    Route::post('/pembayaran/reminder-whatsapp/all', [PembayaranController::class, 'sendWhatsappReminderAll'])->name('pembayaran.reminder-whatsapp.all');
    // alias for compatibility with older view named route
    Route::post('/pembayaran/reminder-whatsapp/bulk', [PembayaranController::class, 'sendWhatsappReminderAll'])->name('pembayaran.reminder-whatsapp.bulk');
    // Route debug: test kirim WA langsung (hanya admin)
    Route::get('/debug/wa-test', function (\App\Services\WhatsAppService $wa) {
        $token    = config('services.whatsapp.token');
        $endpoint = config('services.whatsapp.endpoint');
        $provider = config('services.whatsapp.provider');
        $enabled  = config('services.whatsapp.enabled');
        $target   = request('to', '6285230236462');
        $result   = null;

        if (request()->has('send')) {
            $result = $wa->send($target, 'TEST dari Portal Desa 🎉 Jika pesan ini masuk, notif WA sudah berfungsi!');
            $lastResponse = $wa->getLastResponse();
        }

        return response()->json([
            'config' => [
                'provider' => $provider,
                'enabled'  => $enabled,
                'endpoint' => $endpoint,
                'token_length' => strlen((string) $token),
                'token_preview' => $token ? substr($token, 0, 8) . '...' : null,
            ],
            'test' => request()->has('send') ? [
                'target'   => $target,
                'sent'     => $result,
                'response' => $lastResponse ?? null,
            ] : 'Tambahkan ?send=1&to=628xxx ke URL ini untuk mengirim test WA',
        ]);
    })->name('debug.wa-test');
    Route::get('/pembayaran/{id}/invoice', [PembayaranController::class, 'invoice'])->name('pembayaran.invoice');
    Route::get('/rekap/warga', [RekapController::class, 'perWarga'])->name('rekap.warga');
    Route::get('/rekap/warga/export-csv', [RekapController::class, 'exportWargaCsv'])->name('rekap.warga.csv');
    Route::get('/rekap/bulan', [RekapController::class, 'perBulan'])->name('rekap.bulan');
    Route::get('/rekap/bulan/export-csv', [RekapController::class, 'exportBulanCsv'])->name('rekap.bulan.csv');
        Route::get('/rekap/tunggakan', [RekapController::class, 'tunggakan'])->name('rekap.tunggakan');
    Route::get('/rekap/tunggakan/export-csv', [RekapController::class, 'exportTunggakanCsv'])->name('rekap.tunggakan.csv');
    Route::get('/laporan/pdf', [RekapController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/admin/aktivitas', [AdminActivityController::class, 'index'])->name('admin.activity.index');
    Route::get('/admin/pengaturan/branding', [SystemSettingController::class, 'edit'])->name('admin.settings.branding.edit');
    Route::put('/admin/pengaturan/branding', [SystemSettingController::class, 'update'])->name('admin.settings.branding.update');
    // Verifikasi user baru
    Route::get('/admin/verifikasi-user', [VerifikasiUserController::class, 'index'])->name('admin.verifikasi-user.index');
    Route::post('/admin/verifikasi-user/{id}/verifikasi', [VerifikasiUserController::class, 'verifikasi'])->name('admin.verifikasi-user.verifikasi');
    Route::post('/admin/verifikasi-user/{id}/tolak', [VerifikasiUserController::class, 'tolak'])->name('admin.verifikasi-user.tolak');

    // Verifikasi Laporan OCR Meter
    Route::get('/admin/meter-verifier', [\App\Http\Controllers\Admin\MeterVerifierController::class, 'index'])->name('admin.meter.verify.index');
    Route::post('/admin/meter-verifier/{id}/approve', [\App\Http\Controllers\Admin\MeterVerifierController::class, 'approve'])->name('admin.meter.verify.approve');
    Route::post('/admin/meter-verifier/{id}/reject', [\App\Http\Controllers\Admin\MeterVerifierController::class, 'reject'])->name('admin.meter.verify.reject');
    Route::post('/admin/meter-verifier/{id}/schedule', [\App\Http\Controllers\Admin\MeterVerifierController::class, 'scheduleAudit'])->name('admin.meter.verify.schedule');
});

Route::middleware('auth')->group(function () {
    Route::get('/bayar/{id}', [MidtransController::class, 'pay'])->name('pembayaran.pay');
    Route::post('/midtrans/cancel/{id}', [MidtransController::class, 'cancel'])->name('midtrans.cancel');
    Route::post('/midtrans/finish', [MidtransController::class, 'finish'])->name('midtrans.finish');
});

Route::match(['get', 'post'], '/midtrans/callback', [MidtransController::class, 'callback'])->name('midtrans.callback');

Route::get('/user/dashboard', [UserDashboardController::class, 'dashboard'])
    ->middleware(['auth', 'role:user']);

Route::get('/user/riwayat', [UserDashboardController::class, 'riwayat'])
    ->middleware(['auth', 'role:user'])
    ->name('user.riwayat');

Route::get('/user/tagihan', [UserDashboardController::class, 'tagihan'])
    ->middleware(['auth', 'role:user'])
    ->name('user.tagihan');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/event-donasi', [UserEventDonasiController::class, 'index'])->name('user.event-donasi.index');
    Route::get('/user/event-donasi/{eventDonasi}', [UserEventDonasiController::class, 'show'])->name('user.event-donasi.show');
    Route::post('/user/event-donasi/{eventDonasi}', [UserEventDonasiController::class, 'store'])->name('user.event-donasi.store');
    Route::get('/user/event-donasi-riwayat', [UserEventDonasiController::class, 'history'])->name('user.event-donasi.history');
    // Self-report meter (user uploads photo + OCR)
    Route::get('/meter/self-report', [SelfReportMeterController::class, 'create'])->name('meter.self-report.create');
    Route::post('/meter/self-report', [SelfReportMeterController::class, 'store'])->name('meter.self-report.store');
    Route::post('/meter/self-report/ocr', [SelfReportMeterController::class, 'ocr'])->name('meter.self-report.ocr');
});

// Public-facing event contribution route (used on the welcome page)
Route::get('/event-donasi/{eventDonasi}/kontribusi-public', [UserEventDonasiController::class, 'show'])
    ->name('event-donasi.kontribusi.public');

use App\Models\EventDonasi;

Route::get('/', function () {
    try {
        $activeEvents = EventDonasi::query()
            ->withSum('kontribusis as total_terkumpul', 'nominal')
            ->where('status', 'aktif')
            ->orderByDesc('id')
            ->limit(3)
            ->get();
    } catch (\Exception $e) {
        $activeEvents = collect([]); // Jika database error (misal di Vercel), kembalikan data kosong
    }

    return view('welcome', compact('activeEvents'));
});

Route::get('/dashboard', function () {
    if (Auth::user()?->role === 'admin') {
        return redirect('/admin/dashboard');
    }

    return redirect('/user/dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Settings Routes
Route::get('/user/settings/set-password', [UserSettingsController::class, 'showSetPassword'])
    ->name('user.settings.set-password');
Route::post('/user/settings/set-password', [UserSettingsController::class, 'storePassword'])
    ->name('user.settings.store-password');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user/settings/change-password', [UserSettingsController::class, 'showChangePassword'])
        ->name('user.settings.change-password');
    Route::post('/user/settings/change-password', [UserSettingsController::class, 'updatePassword'])
        ->name('user.settings.update-password');
});

require __DIR__.'/auth.php';


