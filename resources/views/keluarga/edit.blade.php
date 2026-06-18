@extends('layouts.app')

@section('title', 'Edit Keluarga')

@section('content')
<style>
    .page-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:24px; margin-top:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .field-label { display:block; font-size:14px; font-weight:700; color:#215d90; margin-bottom:4px; }
    .field-input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:10px 12px; }
    .btn-primary { background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; border:0; padding:10px 16px; border-radius:12px; font-weight:800; }
    .btn-danger { background:#ef4444; color:#fff; border:0; padding:10px 16px; border-radius:12px; font-weight:800; }
    .btn-secondary { display:inline-flex; align-items:center; border:1px solid #cfe0f1; color:#215d90; padding:10px 16px; border-radius:12px; font-weight:800; text-decoration:none; background:#fff; }
</style>

<div class="page-wrap">
    <div class="hero-card">
        <h2 class="text-2xl font-semibold" style="margin:0;">Edit Data Keluarga</h2>
        <p class="text-sm" style="margin:6px 0 0;color:rgba(255,255,255,.84);">Perbarui data keluarga sesuai kebutuhan.</p>
    </div>

    <div class="panel-card space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Form Keluarga</h2>
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

        <form action="{{ route('keluarga.update', $keluarga->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="field-label">No. KK</label>
                <input
                    type="text"
                    name="no_kk"
                    value="{{ old('no_kk', $keluarga->no_kk) }}"
                    required
                    class="field-input"
                >
            </div>

            <div>
                <label class="field-label">Nama Keluarga</label>
                <input
                    type="text"
                    name="nama_keluarga"
                    value="{{ old('nama_keluarga', $keluarga->nama_keluarga) }}"
                    required
                    class="field-input"
                >
            </div>

            <div>
                <label class="field-label">Alamat</label>
                <textarea
                    name="alamat"
                    rows="3"
                    class="field-input"
                >{{ old('alamat', $keluarga->alamat) }}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-600 mb-3">Penghapusan akan ditolak jika keluarga masih dipakai oleh data warga atau user.</div>
                <div class="flex items-center gap-3 flex-wrap">
                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Simpan
                    </button>
                    <button
                        type="button"
                        onclick="if(confirm('Yakin ingin menghapus data keluarga ini?')) document.getElementById('delete-keluarga-form').submit();"
                        class="btn-danger"
                    >
                        Hapus
                    </button>
                    <a
                        href="{{ route('keluarga.index') }}"
                        class="btn-secondary"
                    >
                        Kembali
                    </a>
                </div>
            </div>
        </form>

        <form id="delete-keluarga-form" action="{{ route('keluarga.destroy', $keluarga->id) }}" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
