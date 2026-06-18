@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 980px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:20px 22px; margin-bottom:18px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .field-label { display:block; font-size:13px; color:#215d90; margin-bottom:5px; font-weight:800; }
    .field-input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:10px 12px; }
    .btn-primary { padding:10px 14px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; cursor:pointer; }
</style>

<div class="page-wrap">
    <div class="hero-card">
        <h2 style="margin:0 0 8px;font-size:26px;font-weight:800;">Pengaturan Branding Desa</h2>
        <p style="margin:0;color:rgba(255,255,255,.84);font-size:14px;">Atur identitas sistem agar tampilan website lebih profesional dan konsisten.</p>
    </div>

    @if(session('success'))
        <div style="background:#ecfeff;border:1px solid #99f6e4;color:#115e59;padding:10px 12px;border-radius:12px;margin-bottom:12px;">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div style="background:#fff7ed;border:1px solid #fed7aa;color:#9a3412;padding:10px 12px;border-radius:12px;margin-bottom:12px;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.branding.update') }}" class="panel-card" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">
        @csrf
        @method('PUT')

        <div>
            <label class="field-label">Nama Aplikasi</label>
            <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" class="field-input" required>
        </div>

        <div>
            <label class="field-label">Nama Desa</label>
            <input type="text" name="village_name" value="{{ old('village_name', $settings['village_name']) }}" class="field-input" required>
        </div>

        <div style="grid-column:1 / -1;">
            <label class="field-label">Tagline</label>
            <input type="text" name="tagline" value="{{ old('tagline', $settings['tagline']) }}" class="field-input">
        </div>

        <div style="grid-column:1 / -1;">
            <label class="field-label">Logo URL (opsional)</label>
            <input type="url" name="logo_url" value="{{ old('logo_url', $settings['logo_url']) }}" class="field-input">
        </div>

        <div>
            <label class="field-label">Kontak Telepon</label>
            <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone']) }}" class="field-input">
        </div>

        <div>
            <label class="field-label">Kontak Email</label>
            <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email']) }}" class="field-input">
        </div>

        <div style="grid-column:1 / -1;">
            <label class="field-label">Alamat Kantor Desa</label>
            <textarea name="address" rows="3" class="field-input">{{ old('address', $settings['address']) }}</textarea>
        </div>

        <div style="grid-column:1 / -1;display:flex;justify-content:flex-end;">
            <button type="submit" class="btn-primary">Simpan Branding</button>
        </div>
    </form>
</div>
@endsection
