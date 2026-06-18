@extends('layouts.app')

@section('content')
<style>
    .password-wrap { max-width: 920px; margin: 0 auto; padding: 24px 16px 34px; }
    .hero-card {
        background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%);
        color: #fff;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 18px 30px rgba(24, 94, 168, .16);
        position: relative;
        overflow: hidden;
    }
    .hero-card::before,
    .hero-card::after { content: ''; position: absolute; border-radius: 999px; background: rgba(255,255,255,.08); }
    .hero-card::before { width: 160px; height: 160px; right: -70px; top: -80px; }
    .hero-card::after { width: 120px; height: 120px; left: -50px; bottom: -60px; }
    .hero-card > * { position: relative; z-index: 1; }
    .panel { margin-top: 16px; background: #fff; border: 1px solid #dce6f1; border-radius: 18px; padding: 22px; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
    .info-box { border: 1px solid #cfe0f1; background: #f0f8ff; color: #215d90; border-radius: 14px; padding: 14px; }
    .field-label { display:block; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: #215d90; margin-bottom: 6px; }
    .field-input { width: 100%; border-radius: 12px; border: 1px solid #cfe0f1; padding: 10px 12px; font-size: 14px; }
    .field-input:focus { border-color: #1d5fb8; box-shadow: 0 0 0 3px rgba(29, 95, 184, .12); }
    .actions { display:flex; gap:10px; margin-top:24px; padding-top:20px; border-top:1px solid #e5edf5; flex-wrap:wrap; }
    .btn-primary { display:inline-flex; align-items:center; padding:10px 20px; border-radius:12px; background:linear-gradient(135deg, #1d5fb8, #14b8a6); color:#fff; font-weight:800; border:0; text-decoration:none; box-shadow:0 10px 18px rgba(29,95,184,.14); }
    .btn-secondary { display:inline-flex; align-items:center; padding:10px 20px; border-radius:12px; border:1px solid #cfe0f1; color:#215d90; text-decoration:none; background:#fff; font-weight:800; }
    .alert-error { border:1px solid #fca5a5; background:#fef2f2; color:#b91c1c; border-radius:14px; padding:14px; }
    .field-note { font-size:12px; color:#64748b; margin-top:6px; }
</style>

<div class="password-wrap">
    <div class="hero-card">
        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-white/80">Keamanan akun</div>
        <h2 class="mt-1 text-3xl font-black">Ganti Password</h2>
        <p class="mt-2 max-w-3xl text-sm text-white/82">Perbarui password akun Anda untuk menjaga akses tetap aman.</p>
    </div>

    <div class="panel space-y-6">
        <div class="info-box text-sm">
            <strong>Catatan:</strong> Setelah password diganti, gunakan password baru saat login berikutnya.
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('user.settings.update-password') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="field-label">Password Saat Ini</label>
                <input type="password" name="current_password" required class="field-input" placeholder="Masukkan password saat ini">
            </div>

            <div>
                <label class="field-label">Password Baru</label>
                <input type="password" name="password" required class="field-input" placeholder="Minimal 8 karakter, huruf besar, angka, dan simbol">
                <p class="field-note">Minimal 8 karakter dengan kombinasi huruf besar, angka, dan simbol</p>
            </div>

            <div>
                <label class="field-label">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" required class="field-input" placeholder="Ulangi password baru">
            </div>

            <div class="actions">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('user.dashboard') }}" class="btn-secondary">Kembali ke Dashboard</a>
            </div>
        </form>
    </div>
</div>
@endsection