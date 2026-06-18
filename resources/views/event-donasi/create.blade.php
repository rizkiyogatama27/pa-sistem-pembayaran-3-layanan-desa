@extends('layouts.app')

@section('title', 'Tambah Event Donasi')

@section('content')
<style>
    .page-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:24px; margin-top:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .field-label { display:block; font-size:14px; font-weight:700; color:#215d90; margin-bottom:4px; }
    .field-input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:10px 12px; }
    .btn-primary { background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:0; padding:10px 16px; border-radius:12px; font-weight:800; }
    .btn-secondary { display:inline-flex; align-items:center; border:1px solid #cfe0f1; color:#215d90; padding:10px 16px; border-radius:12px; font-weight:800; text-decoration:none; background:#fff; }
</style>

<div class="page-wrap">
    <div class="hero-card">
        <h2 class="text-2xl font-semibold" style="margin:0;">Tambah Event Donasi</h2>
        <p class="text-sm" style="margin:6px 0 0;color:rgba(255,255,255,.84);">Buat program donasi atau crowdfunding baru.</p>
    </div>

    <div class="panel-card space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Form Event Donasi</h2>
        </div>

        @if ($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('event-donasi.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf

            <div>
                <label class="field-label">Nama Event</label>
                <input type="text" name="nama_event" value="{{ old('nama_event') }}" required class="field-input" placeholder="Contoh: Donasi Pembangunan Mushola">
            </div>

            <div>
                <label class="field-label">Tujuan</label>
                <textarea name="tujuan" rows="3" required class="field-input" placeholder="Jelaskan tujuan penggalangan dana">{{ old('tujuan') }}</textarea>
            </div>

            <div>
                <label class="field-label">Gambar Cover</label>
                <input type="file" name="cover_image" accept="image/*" class="field-input">
                <p class="text-xs text-gray-500 mt-1">Opsional. Upload gambar dari file.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Target Dana</label>
                    <input type="number" name="target_dana" value="{{ old('target_dana', 0) }}" min="0" required class="field-input">
                </div>
                <div>
                    <label class="field-label">Status</label>
                    <select name="status" class="field-input">
                        <option value="draft" @selected(old('status', 'draft') === 'draft')>Draft</option>
                        <option value="aktif" @selected(old('status') === 'aktif')>Aktif</option>
                        <option value="selesai" @selected(old('status') === 'selesai')>Selesai</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="field-input">
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <div class="flex items-center gap-3 flex-wrap">
                    <button type="submit" class="btn-primary">Simpan</button>
                    <a href="{{ route('event-donasi.index') }}" class="btn-secondary">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
