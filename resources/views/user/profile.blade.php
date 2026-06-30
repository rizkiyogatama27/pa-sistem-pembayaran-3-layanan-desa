@extends('layouts.app')

@section('title', 'Profil Saya - Portal Desa')

@section('content')
<style>
    .page-wrap { max-width: 760px; margin: 0 auto; padding: 28px 16px 48px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:24px 28px; display:flex; align-items:center; gap:20px; flex-wrap:wrap; box-shadow:0 18px 30px rgba(24,94,168,.16); margin-bottom:24px; }
    .avatar { width:70px; height:70px; border-radius:50%; background:rgba(255,255,255,.2); display:grid; place-items:center; font-size:30px; font-weight:900; color:#fff; border:3px solid rgba(255,255,255,.4); flex:0 0 auto; }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:hidden; box-shadow:0 8px 22px rgba(15,23,42,.06); }
    .panel-head { padding:20px 24px; border-bottom:1px solid #e5edf5; font-size:16px; font-weight:800; color:#0f172a; }
    .panel-body { padding:24px; }
    .field-group { margin-bottom:20px; }
    .field-label { display:block; font-size:13px; font-weight:700; color:#334155; margin-bottom:6px; }
    .field-input { width:100%; padding:10px 14px; border:1px solid #cbd5e1; border-radius:10px; font-size:14px; color:#0f172a; background:#f8fafc; transition:border-color 0.2s; }
    .field-input:focus { outline:none; border-color:#1d4ed8; background:#fff; box-shadow:0 0 0 3px rgba(29,78,216,.1); }
    .field-input:disabled { background:#f1f5f9; color:#64748b; cursor:not-allowed; }
    .btn-save { padding:11px 28px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:none; border-radius:12px; font-weight:800; font-size:14px; cursor:pointer; transition:all 0.2s; }
    .btn-save:hover { transform:translateY(-2px); box-shadow:0 8px 16px rgba(29,78,216,.25); }
    .info-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:700; }
    .badge-readonly { background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd; }
    .hint-text { font-size:12px; color:#94a3b8; margin-top:5px; }
</style>

<div class="page-wrap">
    {{-- Hero --}}
    <div class="hero-card">
        <div class="avatar">
            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
        </div>
        <div>
            <h1 style="margin:0 0 4px;font-size:22px;font-weight:900;">{{ Auth::user()->name }}</h1>
            <div style="font-size:13px;color:rgba(255,255,255,.8);">{{ Auth::user()->email }}</div>
            @if($warga)
                <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap;">
                    <span class="info-badge badge-readonly">NIK: {{ $warga->nik }}</span>
                    @if($warga->no_hp)
                        <span class="info-badge badge-readonly">📱 {{ $warga->no_hp }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div style="background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:12px;padding:14px 18px;margin-bottom:20px;font-weight:600;">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:14px 18px;margin-bottom:20px;font-weight:600;">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Data Warga (Read Only) --}}
    <div class="panel-card" style="margin-bottom:20px;">
        <div class="panel-head">📋 Data Kependudukan (Hanya Bisa Diubah Admin)</div>
        <div class="panel-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="field-group">
                    <label class="field-label">Nama Lengkap</label>
                    <input class="field-input" type="text" value="{{ $warga->nama ?? '-' }}" disabled>
                </div>
                <div class="field-group">
                    <label class="field-label">NIK</label>
                    <input class="field-input" type="text" value="{{ $warga->nik ?? '-' }}" disabled>
                </div>
                <div class="field-group" style="grid-column:1/-1;">
                    <label class="field-label">Nama Kepala Keluarga</label>
                    <input class="field-input" type="text" value="{{ $warga->keluarga?->nama_keluarga ?? '-' }}" disabled>
                </div>
            </div>
            <p class="hint-text" style="margin-top:0;">ℹ️ Data di atas hanya dapat diubah oleh Admin Desa. Hubungi petugas balai desa jika terdapat kesalahan data.</p>
        </div>
    </div>

    {{-- Data Kontak (Bisa Diubah Sendiri) --}}
    @if($warga)
    <div class="panel-card">
        <div class="panel-head">✏️ Perbarui Kontak & Alamat</div>
        <div class="panel-body">
            <p style="font-size:13px;color:#64748b;margin:0 0 20px;">Perbarui nomor HP dan alamat Anda sendiri. Nomor HP yang benar penting agar Anda menerima pengingat tagihan melalui WhatsApp.</p>
            <form method="POST" action="{{ route('user.profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="field-group">
                    <label class="field-label" for="no_hp">📱 Nomor WhatsApp / HP</label>
                    <input id="no_hp" class="field-input" type="text" name="no_hp" value="{{ old('no_hp', $warga->no_hp) }}" placeholder="Contoh: 628123456789 (format internasional)">
                    @error('no_hp')<div class="hint-text" style="color:#ef4444;">{{ $message }}</div>@enderror
                    <div class="hint-text">⚠️ Masukkan nomor tanpa tanda + dan tanpa strip. Contoh: 628123456789</div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="alamat">🏠 Alamat Rumah</label>
                    <textarea id="alamat" class="field-input" name="alamat" rows="3" placeholder="Masukkan alamat lengkap Anda">{{ old('alamat', $warga->alamat) }}</textarea>
                    @error('alamat')<div class="hint-text" style="color:#ef4444;">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-save">💾 Simpan Perubahan</button>
            </form>
        </div>
    </div>
    @else
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:20px 24px;color:#92400e;">
        <p style="margin:0;font-weight:700;">⚠️ Akun Anda belum dihubungkan dengan data warga.</p>
        <p style="margin:8px 0 0;font-size:13px;">Harap hubungi Admin Desa untuk menghubungkan akun Anda dengan data kependudukan.</p>
    </div>
    @endif
</div>
@endsection
