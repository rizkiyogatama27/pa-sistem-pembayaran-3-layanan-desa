@extends('layouts.app')

@section('content')
<style>
    .page-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px 40px; }
    .hero-card { background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; border-radius:20px; padding:18px 20px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
    .panel-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; padding:24px; margin-top:16px; box-shadow:0 10px 22px rgba(15,23,42,.05); }
    .field-label { display:block; font-size:14px; font-weight:700; color:#215d90; margin-bottom:4px; }
    .field-input { width:100%; border:1px solid #cfe0f1; border-radius:12px; padding:10px 12px; }
    .btn-primary { display:inline-block; padding:10px 16px; border-radius:12px; background:linear-gradient(135deg,#1d5fb8,#14b8a6); color:#fff; font-weight:800; border:0; cursor:pointer; }
    .btn-secondary { display:inline-block; padding:10px 16px; border-radius:12px; border:1px solid #cfe0f1; color:#215d90; text-decoration:none; background:#fff; font-weight:800; }
</style>

<div class="page-wrap">
    <div class="hero-card">
        <h2 class="text-2xl font-semibold" style="margin:0;">Edit Pembayaran</h2>
        <p class="text-sm" style="margin:6px 0 0;color:rgba(255,255,255,.84);">Perbarui data pembayaran yang sudah dibuat.</p>
    </div>

    <div class="panel-card space-y-6">
        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('pembayaran.update.post', $pembayaran->id) }}" method="POST" class="space-y-4">
            @csrf
            @php
                $selectedJenis = $jenisPembayarans->firstWhere('id', $pembayaran->jenis_pembayaran_id);
                $isAir = $selectedJenis ? str_contains(strtolower($selectedJenis->nama), 'air') : false;
            @endphp

            <div>
                <label class="field-label">Warga</label>
                <select name="warga_id" required class="field-input">
                    @foreach($wargas as $w)
                        <option value="{{ $w->id }}" @selected($pembayaran->warga_id == $w->id)>{{ $w->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="field-label">Jenis Pembayaran</label>
                <select name="jenis_pembayaran_id" id="jenis_pembayaran_id" onchange="toggleAirFields()" required class="field-input">
                    @foreach($jenisPembayarans as $j)
                        <option value="{{ $j->id }}" data-nama="{{ $j->nama }}" @selected($pembayaran->jenis_pembayaran_id == $j->id)>{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div id="air-fields" class="space-y-4 {{ $isAir ? '' : 'hidden' }}">
                <div class="rounded-lg border border-sky-100 bg-sky-50 p-4">
                    <p class="text-sm font-semibold text-sky-900">Tagihan HIPPAM / Air</p>
                    <p class="text-xs text-sky-700 mt-1">Nilai jumlah dihitung otomatis dari meter awal/akhir. Denda otomatis Rp 2.500 jika sudah lewat jatuh tempo.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Periode</label>
                        <input type="month" name="periode" value="{{ old('periode', $pembayaran->periode) }}" required class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Jatuh Tempo</label>
                        <input type="date" id="jatuh_tempo" name="jatuh_tempo" value="{{ old('jatuh_tempo', optional($pembayaran->jatuh_tempo)->toDateString()) }}" required class="field-input">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="field-label">Meter Awal</label>
                        <input type="number" name="meter_awal" value="{{ old('meter_awal', $pembayaran->meter_awal) }}" min="0" required class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Meter Akhir</label>
                        <input type="number" name="meter_akhir" value="{{ old('meter_akhir', $pembayaran->meter_akhir) }}" min="0" required class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Denda</label>
                        <input type="number" id="denda" name="denda" value="{{ old('denda', $pembayaran->denda ?? 0) }}" min="0" class="field-input">
                        <p class="mt-1 text-xs text-gray-500">Otomatis Rp 2.500 saat lewat jatuh tempo. Bisa diubah manual.</p>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                    <div class="font-semibold text-gray-900">Rumus otomatis</div>
                    <div class="mt-1">Pemakaian x Rp 1.500 + biaya tetap Rp 5.000 + denda</div>
                </div>
                
                <div class="mt-4 p-4 rounded-lg bg-indigo-50 border border-indigo-200">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-indigo-900">Estimasi Total Tagihan:</span>
                        <span class="text-xl font-extrabold text-indigo-700" id="estimasi-total">Rp 0</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Tanggal Bayar</label>
                    <input type="date" name="tanggal_bayar" value="{{ old('tanggal_bayar', optional($pembayaran->tanggal_bayar)->toDateString()) }}" required class="field-input">
                </div>
                <div id="manual-jumlah-wrapper" class="{{ $isAir ? 'hidden' : '' }}">
                    <label class="field-label">Jumlah</label>
                    <input type="number" name="jumlah" value="{{ $pembayaran->jumlah }}" min="1" class="field-input" @if($isAir) disabled @endif>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <input type="hidden" name="status" value="{{ $pembayaran->status }}">
                <div class="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                    {{ $pembayaran->status === 'paid' ? 'Lunas' : 'Pending / Draft' }}
                </div>
                <p class="mt-1 text-xs text-gray-500">Status dikunci. Ubah status hanya lewat proses bayar online atau tunai.</p>
            </div>

            <div>
                <label class="field-label">Keterangan</label>
                <textarea name="keterangan" rows="4" class="field-input">{{ $pembayaran->keterangan }}</textarea>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px; padding-top:20px; border-top:1px solid #e5e7eb;">
                <button type="submit" class="btn-primary">
                    Simpan Perubahan
                </button>
                <a href="{{ route('pembayaran.index') }}" class="btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAirFields() {
    const jenisSelect = document.getElementById('jenis_pembayaran_id');
    const selected = jenisSelect.options[jenisSelect.selectedIndex];
    const jenisNama = (selected?.dataset?.nama || '').toLowerCase();
    const isAir = jenisNama.includes('air');
    document.getElementById('air-fields').classList.toggle('hidden', !isAir);
    const jumlahInput = document.querySelector('input[name="jumlah"]');
    const jumlahWrapper = document.getElementById('manual-jumlah-wrapper');
    jumlahWrapper.classList.toggle('hidden', isAir);
    if (jumlahInput) {
        jumlahInput.disabled = isAir;
    }
    if (isAir) calculateAirTotal();
}

function calculateAirTotal() {
    const awal = parseInt(document.querySelector('input[name="meter_awal"]').value) || 0;
    const akhir = parseInt(document.querySelector('input[name="meter_akhir"]').value) || 0;
    const denda = parseInt(document.getElementById('denda').value) || 0;
    
    let pemakaian = akhir - awal;
    if (pemakaian < 0) pemakaian = 0;
    
    const total = (pemakaian * 1500) + 5000 + denda;
    
    const estimasiEl = document.getElementById('estimasi-total');
    if (estimasiEl) {
        estimasiEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('input[name="meter_awal"]')?.addEventListener('input', calculateAirTotal);
    document.querySelector('input[name="meter_akhir"]')?.addEventListener('input', calculateAirTotal);
    document.getElementById('denda')?.addEventListener('input', calculateAirTotal);
    
    if (!document.getElementById('air-fields').classList.contains('hidden')) {
        calculateAirTotal();
    }
});
</script>

@endsection