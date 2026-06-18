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

<div class="page-wrap space-y-6">
    <div class="hero-card">
        <h2 style="margin:0 0 6px;font-size:24px;font-weight:800;">Bayar Tunai</h2>
        <p style="margin:0;font-size:14px;color:rgba(255,255,255,.84);">Proses pembayaran manual menggunakan uang tunai.</p>
    </div>

    @if(session('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel-card space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-gray-500">Warga</div>
                <div class="font-semibold text-gray-900">{{ $pembayaran->warga->nama ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">Invoice</div>
                <div class="font-semibold text-gray-900">{{ $pembayaran->invoice ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">Jenis Pembayaran</div>
                <div class="font-semibold text-gray-900">{{ $pembayaran->jenisPembayaran->nama ?? '-' }}</div>
            </div>
            <div>
                <div class="text-gray-500">Total Tagihan</div>
                <div class="font-semibold text-gray-900">Rp {{ number_format((int) $pembayaran->jumlah, 0, ',', '.') }}</div>
            </div>
        </div>

        <form action="{{ route('pembayaran.cash.pay', $pembayaran->id) }}" method="POST" class="space-y-4" id="cash-form">
            @csrf
            <input type="hidden" id="total_tagihan_value" value="{{ (int) $pembayaran->jumlah }}">
            <div>
                <label class="field-label">Uang Diterima (Tunai)</label>
                <input
                    type="number"
                    min="0"
                    required
                    name="cash_received_amount"
                    id="cash_received_amount"
                    value="{{ old('cash_received_amount', (int) $pembayaran->jumlah) }}"
                    class="field-input"
                >
            </div>

            <div>
                <label class="field-label">Catatan Tunai (Opsional)</label>
                <textarea
                    name="catatan_tunai"
                    rows="3"
                    class="field-input"
                    placeholder="Contoh: Dibayar di kantor desa"
                >{{ old('catatan_tunai') }}</textarea>
            </div>

            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                Kembalian: <span id="cash-change" class="font-semibold">Rp 0</span>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px; padding-top:20px; border-top:1px solid #e5e7eb;">
                <button 
                    type="submit" 
                    class="btn-primary"
                >
                    Konfirmasi Bayar Tunai
                </button>
                <a href="{{ route('pembayaran.index') }}" class="btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const total = Number(document.getElementById('total_tagihan_value').value || 0);
    const input = document.getElementById('cash_received_amount');
    const changeEl = document.getElementById('cash-change');

    function formatRupiah(value) {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function updateChange() {
        const received = Number(input.value || 0);
        const change = received - total;
        changeEl.textContent = formatRupiah(Math.max(change, 0));
    }

    input.addEventListener('input', updateChange);
    updateChange();
})();
</script>
@endsection
