@extends('layouts.app')

@section('content')
<style>
    .portal-wrap { max-width: 760px; margin: 0 auto; padding: 24px 16px 34px; }
    .portal-title { font-size: 24px; font-weight: 900; color: #0f766e; letter-spacing: .01em; }
    .portal-subtitle { font-size: 15px; color: #4b5563; margin-top: 4px; }
    .hero-card { background: linear-gradient(135deg, #1aa77f 0%, #2563eb 100%); color: #fff; border-radius: 18px; padding: 18px; box-shadow: 0 10px 24px rgba(21, 126, 98, .13); display: flex; align-items: center; gap: 18px; position: relative; overflow: hidden; }
    .hero-card::before { content: ''; position: absolute; width: 180px; height: 180px; right: -80px; top: -70px; border-radius: 999px; background: rgba(255,255,255,.08); }
    .hero-card > * { position: relative; z-index: 1; }
    .hero-logo { width: 54px; height: 54px; background: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(16, 185, 129, .13); flex: 0 0 auto; }
    .hero-logo img { width: 38px; height: 38px; }
    .hero-info { flex: 1; }
    .hero-tag { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; padding: 4px 10px; border-radius: 999px; background: rgba(255,255,255,.16); color: #fff; font-size: 12px; font-weight: 700; }
    .form-card { margin-top: 18px; background: #fff; border: 1.5px solid #dbeafe; border-radius: 16px; padding: 16px; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
    .form-title { font-size: 16px; font-weight: 900; color: #18324d; margin-bottom: 4px; }
    .form-subtitle { font-size: 13px; color: #64748b; margin-bottom: 14px; }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-size: 12px; font-weight: 800; letter-spacing: .03em; color: #2563eb; margin-bottom: 6px; text-transform: uppercase; }
    .field input, .field select, .field textarea { width: 100%; border: 1.5px solid #93c5fd; border-radius: 10px; padding: 10px 12px; font-size: 14px; background: #fff; color: #1e293b; }
    .field textarea { min-height: 110px; resize: vertical; }
    .field input:focus, .field select:focus, .field textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .12); }
    .btn-primary { display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; border-radius: 10px; background: #2563eb; color: #fff; text-decoration: none; border: 1px solid #2563eb; font-size: 14px; font-weight: 800; transition: background .2s; }
    .btn-primary:hover { background: #1d4ed8; }
    .btn-secondary { display: inline-flex; align-items: center; justify-content: center; padding: 10px 16px; border-radius: 10px; background: #fff; color: #2563eb; text-decoration: none; border: 1.5px solid #93c5fd; font-size: 14px; font-weight: 800; transition: background .2s; }
    .btn-secondary:hover { background: #f8fafc; }
    .portal-empty { margin-top: 12px; border: 1.5px dashed #fca5a5; background: #fef2f2; color: #b91c1c; padding: 12px 14px; border-radius: 12px; }
    .status-box { margin-top: 12px; border: 1.5px solid #bbf7d0; background: #f0fdf4; color: #166534; padding: 12px 14px; border-radius: 12px; }
    .preview-box { margin-top: 10px; display: none; border: 1px solid #dbeafe; border-radius: 12px; padding: 10px; background: #f8fafc; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    @media (max-width: 720px) {
        .hero-card { flex-direction: column; align-items: flex-start; gap: 10px; }
    }
</style>

<div class="portal-wrap">
    <div class="portal-title">Lapor Meter Mandiri</div>
    <div class="portal-subtitle">Upload foto meter, baca hasil OCR, lalu kirim untuk verifikasi.</div>

    <div class="hero-card" style="margin-top: 18px;">
        <div class="hero-logo">
            <img src="/logo-simp-mld.png" alt="Logo" />
        </div>
        <div class="hero-info">
            <div style="font-size: 15px; opacity: .97; font-weight:700;">OCR Meter Air</div>
            <div style="font-size: 28px; font-weight: 900; line-height: 1.2; margin-top: 2px; color:#fff;">{{ auth()->user()->name }}</div>
            <div class="hero-tag">Pilih tagihan air, unggah foto, lalu sistem membaca angka meter</div>
        </div>
    </div>

    @if(session('status'))
        <div class="status-box">{{ session('status') }}</div>
    @endif

    @if(empty($pembayarans) || ($pembayarans instanceof \Illuminate\Support\Collection && $pembayarans->isEmpty()))
        <div class="portal-empty">Belum ada tagihan air yang tersedia untuk akun ini.</div>
    @endif

    <div class="form-card">
        <div class="form-title">Form OCR Meter</div>
        <div class="form-subtitle">Unggah foto meter untuk dihitung otomatis oleh sistem.</div>

        @if($errors->any())
            <div style="margin-bottom: 14px; padding: 12px; border-radius: 10px; background: #fef2f2; border: 1.5px dashed #fca5a5; color: #b91c1c; font-size: 13px;">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('meter.self-report.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label>Pilih Tagihan (Pembayaran)</label>
                <select name="pembayaran_id" required>
                    @php $selectedPembayaran = old('pembayaran_id') ?: request('pembayaran_id'); @endphp
                    @forelse(($pembayarans ?? collect()) as $p)
                        <option value="{{ $p->id }}" @selected((string) $p->id === (string) $selectedPembayaran)>{{ $p->invoice }} - Periode {{ $p->periode }} - Status: {{ $p->status }}</option>
                    @empty
                        <option value="">Belum ada tagihan air yang tersedia</option>
                    @endforelse
                </select>
            </div>

            <div class="field">
                <label>Foto meter <span style="text-transform:none;font-weight:400;color:#64748b;">(Unggah foto untuk membaca meteran otomatis)</span></label>
                <input type="file" name="meter_photo" accept="image/*" required />
                <div class="preview-box" id="photo-preview"></div>
                <div id="ocr-loading" style="display: none; font-size: 13px; color: #2563eb; margin-top: 8px; font-weight: 700;">
                    <svg style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:4px;animation:spin 1s linear infinite;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Sedang membaca angka meter dari foto...
                </div>
            </div>

            <div class="field" id="meter-field" style="display: {{ old('meter_akhir') ? 'block' : 'none' }};">
                <label>Meter akhir (Otomatis)</label>
                <input type="number" id="meter_akhir_input" name="meter_akhir" min="0" value="{{ old('meter_akhir') }}" {{ old('meter_akhir') ? '' : 'readonly' }} />
                <div id="ocr-msg" style="font-size: 12px; margin-top: 6px; color: #166534;"></div>
            </div>

            <div class="field">
                <label>Catatan (opsional)</label>
                <textarea name="notes" placeholder="Misalnya: meter difoto malam hari, angka agak silau.">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <button class="btn-primary" type="submit">Kirim Laporan</button>
                <a href="{{ route('user.tagihan') }}" class="btn-secondary">Kembali ke Tagihan</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[type="file"][name="meter_photo"]');
    const preview = document.getElementById('photo-preview');

    if (!input || !preview) return;

    input.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) {
            preview.style.display = 'none';
            preview.innerHTML = '';
            document.getElementById('meter-field').style.display = 'none';
            return;
        }

        const url = URL.createObjectURL(file);
        preview.style.display = 'block';
        preview.innerHTML = '<img src="' + url + '" alt="Preview foto meter" style="width:100%;max-height:320px;object-fit:cover;border-radius:10px;">';

        const formData = new FormData();
        formData.append('meter_photo', file);
        formData.append('_token', '{{ csrf_token() }}');

        document.getElementById('ocr-loading').style.display = 'block';
        document.getElementById('meter-field').style.display = 'none';
        document.querySelector('.btn-primary').disabled = true;

        fetch('{{ route("meter.self-report.ocr") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('ocr-loading').style.display = 'none';
            document.getElementById('meter-field').style.display = 'block';
            document.querySelector('.btn-primary').disabled = false;
            const msgBox = document.getElementById('ocr-msg');

            if (data.status === 'ok' && data.meter_akhir !== null) {
                document.getElementById('meter_akhir_input').value = data.meter_akhir;
                document.getElementById('meter_akhir_input').readOnly = true;
                document.getElementById('meter_akhir_input').style.backgroundColor = '#f1f5f9';
                msgBox.style.color = '#166534';
                msgBox.innerText = 'Berhasil membaca meteran dari foto. Jika kurang tepat, admin akan menyesuaikan saat verifikasi.';
            } else {
                document.getElementById('meter_akhir_input').value = '';
                document.getElementById('meter_akhir_input').readOnly = false;
                document.getElementById('meter_akhir_input').style.backgroundColor = '#fff';
                msgBox.style.color = '#b91c1c';
                msgBox.innerText = 'Gagal membaca meteran otomatis. Silakan isi angka secara manual.';
            }
        })
        .catch(err => {
            document.getElementById('ocr-loading').style.display = 'none';
            document.getElementById('meter-field').style.display = 'block';
            document.querySelector('.btn-primary').disabled = false;
            document.getElementById('meter_akhir_input').readOnly = false;
            document.getElementById('meter_akhir_input').style.backgroundColor = '#fff';
            document.getElementById('ocr-msg').style.color = '#b91c1c';
            document.getElementById('ocr-msg').innerText = 'Terjadi kesalahan. Silakan isi angka secara manual.';
        });
    });
});
</script>
@endpush

@endsection
