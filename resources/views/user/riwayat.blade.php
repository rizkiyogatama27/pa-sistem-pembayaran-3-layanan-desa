@extends('layouts.app')

@section('content')
<style>
    .portal-wrap { max-width: 1120px; margin: 0 auto; padding: 24px 16px 34px; }
    .portal-title { font-size: 24px; font-weight: 900; color: #18324d; letter-spacing: .01em; }
    .portal-subtitle { font-size: 15px; color: #4b5563; margin-top: 4px; }
    .hero-card {
        background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%);
        color: #fff;
            border-radius: 18px;
        padding: 20px;
        box-shadow: 0 18px 30px rgba(24, 94, 168, .16);
        position: relative;
        overflow: hidden;
    }
    .hero-card::before,
    .hero-card::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        background: rgba(255,255,255,.08);
    }
    .hero-card::before { width: 180px; height: 180px; right: -70px; top: -90px; }
    .hero-card::after { width: 140px; height: 140px; left: -70px; bottom: -70px; }
    .hero-card > * { position: relative; z-index: 1; }
    .hero-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; }
        .hero-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; align-items: stretch; }
        .hero-mini { background: rgba(255,255,255,.14); border-radius: 14px; padding: 14px; border: 1px solid rgba(255,255,255,.14); min-height: 88px; display: flex; flex-direction: column; justify-content: center; }
    .hero-mini b { display: block; font-size: 24px; line-height: 1.1; }
    .hero-mini small { color: rgba(255,255,255,.86); }
    .section-label { font-size: 13px; margin: 18px 0 10px; font-weight: 800; letter-spacing: .06em; color: #52606b; text-transform: uppercase; }
    .riwayat-list { display: grid; gap: 10px; }
    .riwayat-card { background: #fff; border: 1px solid #dce6f1; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
        .riwayat-card { background: #fff; border: 1px solid #dce6f1; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 22px rgba(15, 23, 42, .05); }
    .riwayat-item { padding: 14px 16px; display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
    .riwayat-item .left { min-width: 0; }
    .riwayat-item .left b { color: #18324d; display: block; font-size: 14px; font-weight: 900; }
    .riwayat-item .left span { color: #6a7782; font-size: 12px; }
    .riwayat-item .right { font-weight: 900; white-space: nowrap; color: #146a2f; text-align: right; }
    .riwayat-foot { padding: 12px 16px; border-top: 1px solid #e5edf5; background: #f8fbfe; }
    .status-pill { display: inline-block; margin-top: 8px; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; background: #d6f5dc; color: #146a2f; }
    .portal-empty { margin-top: 12px; border: 1.5px dashed #a7c3df; background: #f0f8ff; color: #215d90; padding: 14px 16px; border-radius: 14px; }
    .btn-kembali { display: inline-flex; margin-top: 10px; padding: 8px 16px; border-radius: 10px; border: 1px solid #cfe0f1; color: #215d90; text-decoration: none; font-size: 14px; font-weight: 800; background: #fff; }
    .btn-kembali:hover { background: #eef6fc; }
    @media (max-width: 720px) {
        .hero-grid { grid-template-columns: 1fr; }
        .riwayat-item { flex-direction: column; align-items: stretch; }
        .riwayat-item .right { text-align: left; }
    }
</style>

<div class="portal-wrap">
    <div class="portal-title">Riwayat Saya</div>
    <div class="portal-subtitle">Daftar pembayaran yang sudah lunas dan tersimpan di akun Anda</div>

    <div class="hero-card" style="margin-top: 18px;">
        <div style="font-size: 15px; opacity: .97; font-weight:800; letter-spacing:.04em; text-transform:uppercase;">Ringkasan Riwayat</div>
        <div style="font-size: 28px; font-weight: 900; line-height: 1.2; margin-top: 2px; color:#fff;">{{ auth()->user()->name }}</div>

        <div class="hero-grid">
            <div class="hero-mini">
                <b>{{ $riwayat->count() }}</b>
                <small>Transaksi lunas</small>
            </div>
            <div class="hero-mini">
                <b>Rp {{ number_format($riwayat->sum('jumlah'), 0, ',', '.') }}</b>
                <small>Total pembayaran</small>
            </div>
            <div class="hero-mini">
                <b>{{ $riwayat->sortByDesc('tanggal_bayar')->first() ? \Illuminate\Support\Carbon::parse($riwayat->sortByDesc('tanggal_bayar')->first()->tanggal_bayar)->translatedFormat('M Y') : '-' }}</b>
                <small>Transaksi terakhir</small>
            </div>
        </div>
    </div>

    <div class="section-label">Transaksi Lunas</div>
    <div class="riwayat-list">
        @forelse($riwayat as $item)
            <div class="riwayat-card">
                <div class="riwayat-item">
                    <div class="left">
                        <b>{{ $item->jenisPembayaran->nama ?? '-' }} - {{ $item->invoice ?? '-' }}</b>
                        <span>{{ \Illuminate\Support\Carbon::parse($item->tanggal_bayar)->translatedFormat('d M Y') }}</span>
                        @if($item->periode || $item->pemakaian_air !== null)
                            <div style="font-size:12px; color:#64748b; margin-top:3px;">
                                {{ $item->periode ?? '-' }}
                                @if($item->pemakaian_air !== null)
                                    | Pemakaian {{ $item->pemakaian_air }} m3
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="right">
                        Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                        <div class="status-pill">Lunas</div>
                    </div>
                </div>
                <div class="riwayat-foot"></div>
            </div>
        @empty
            <div class="portal-empty">Belum ada data riwayat pembayaran.</div>
        @endforelse
    </div>

    <a href="{{ route('user.tagihan') }}" class="btn-kembali">Kembali ke Tagihan</a>
</div>
@endsection