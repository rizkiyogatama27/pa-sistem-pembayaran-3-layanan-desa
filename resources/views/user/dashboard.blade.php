@extends('layouts.app')

@section('content')
<style>
    .portal-wrap { max-width: 980px; margin: 0 auto; padding: 24px 16px 34px; }
    .portal-title { font-size: 24px; font-weight: 800; color: #18324d; }
    .portal-subtitle { font-size: 14px; color: #5e6b73; margin-top: 4px; }
    .hero-card { 
        background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%);
        color: #fff; 
        border-radius: 16px; 
        padding: 18px; 
        box-shadow: 0 18px 30px rgba(24, 94, 168, .18);
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
    .hero-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; align-items: stretch; }
    .hero-mini { background: rgba(255,255,255,.14); border-radius: 14px; padding: 12px; border: 1px solid rgba(255,255,255,.14); min-height: 88px; display: flex; flex-direction: column; justify-content: center; }
    .hero-mini b { display: block; font-size: 24px; line-height: 1; }
    .quick-menu { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; }
    .quick-menu a { text-decoration: none; color: inherit; }
    .quick-card { background: #fff; border: 1px solid #dce6f1; border-radius: 16px; padding: 14px; min-height: 144px; box-shadow: 0 8px 20px rgba(15, 23, 42, .05); transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease; display: flex; flex-direction: column; }
    .quick-card:hover { transform: translateY(-2px); border-color: #b8c8dc; box-shadow: 0 14px 26px rgba(15, 23, 42, .08); }
    .quick-icon { width: 36px; height: 36px; border-radius: 12px; display: grid; place-items: center; background: linear-gradient(135deg, #eef2ff, #dbeafe); color: #1d5fb8; font-size: 17px; margin-bottom: 10px; }
    .quick-title { margin: 0; font-size: 14px; font-weight: 800; color: #18324d; }
    .quick-desc { margin: 4px 0 0; font-size: 12px; color: #64748b; line-height: 1.45; }
    .section-label { font-size: 13px; margin: 18px 0 10px; font-weight: 800; letter-spacing: .06em; color: #52606b; text-transform: uppercase; }
    .layanan-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; align-items: stretch; }
    .layanan-card { border-radius: 16px; padding: 16px; border: 1px solid #dce6f1; background: #fff; box-shadow: 0 8px 20px rgba(15, 23, 42, .05); transition: transform .15s ease, border-color .15s ease; min-height: 236px; display: flex; flex-direction: column; }
    .layanan-card:hover { transform: translateY(-2px); border-color: #b8c8dc; }
    .layanan-card h4 { margin: 0; font-size: 22px; line-height: 1; }
    .layanan-card h5 { margin: 8px 0 4px; font-size: 22px; color: #18324d; }
    .layanan-card p { margin: 0; color: #61707c; font-size: 13px; }
    .status-pill { display: inline-block; margin-top: 10px; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 700; }
    .pill-pending { background: #fdecc8; color: #9a4a00; }
    .pill-paid { background: #d6f5dc; color: #146a2f; }
    .riwayat-list { display: grid; gap: 10px; }
    .riwayat-item { background: #fff; border: 1px solid #dde5ec; border-radius: 16px; padding: 14px 16px; display: flex; justify-content: space-between; gap: 10px; box-shadow: 0 8px 20px rgba(15, 23, 42, .04); align-items: center; }
    .riwayat-item .left { min-width: 0; }
    .riwayat-item .left b { color: #1f2c37; display: block; font-size: 14px; }
    .riwayat-item .left span { color: #6a7782; font-size: 12px; }
    .riwayat-item .right { font-weight: 800; white-space: nowrap; }
    .riwayat-item .right.amount-paid { color: #146a2f; }
    .riwayat-item .right.amount-unpaid { color: #b42318; }
    .portal-empty { margin-top: 12px; border: 1px dashed #f1b177; background: #fff5ec; color: #9b4e17; padding: 10px 12px; border-radius: 10px; }
    .section-link { font-size: 12px; text-decoration: none; color: #1d5fb8; }
    .event-cover { width: 120px; height: 120px; border-radius: 12px; object-fit: cover; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
    .transaction-card {
        display: flex; align-items: center; padding: 22px 24px; background: #ffffff;
        border-radius: 24px; text-decoration: none; position: relative; overflow: hidden;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); margin-bottom: 16px;
        box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04), inset 0 0 0 1px rgba(15, 23, 42, 0.04);
    }
    .transaction-card::before {
        content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 6px;
        background: var(--accent-color, #cbd5e1); border-radius: 24px 0 0 24px;
        transition: width 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .transaction-card:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08), inset 0 0 0 1px rgba(15, 23, 42, 0.04);
    }
    .transaction-card:hover::before { width: 100%; opacity: 0.04; }
    .transaction-icon {
        width: 56px; height: 56px; border-radius: 18px; display: flex; align-items: center; justify-content: center; margin-right: 20px; flex-shrink: 0; z-index: 1;
        box-shadow: 0 10px 20px var(--shadow-color, rgba(0,0,0,0.1)), inset 0 2px 0 rgba(255,255,255,0.3);
    }
    .transaction-icon svg { width: 28px; height: 28px; stroke-width: 2.5; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); }
    .transaction-info { flex: 1; min-width: 0; z-index: 1; }
    .transaction-title { display: flex; align-items: center; gap: 12px; font-size: 17px; font-weight: 900; color: #0f172a; margin-bottom: 6px; letter-spacing: -0.01em; }
    .transaction-date { font-size: 14px; color: #64748b; font-weight: 600; display:flex; align-items:center; gap:8px;}
    .transaction-amount-wrapper { text-align: right; margin-left: 20px; flex-shrink: 0; z-index: 1; }
    .transaction-amount { font-size: 20px; font-weight: 900; letter-spacing: -0.04em; margin-bottom: 8px; display:block; }
    .transaction-amount.paid { color: #059669; }
    .transaction-amount.unpaid { color: #dc2626; }
    .transaction-status { font-size: 11px; font-weight: 900; padding: 6px 12px; border-radius: 8px; display: inline-block; text-transform: uppercase; letter-spacing: 0.1em; }
    .transaction-status.paid { background: rgba(16, 185, 129, 0.1); color: #059669; box-shadow: inset 0 0 0 1px rgba(16,185,129,0.2); }
    .transaction-status.unpaid { background: rgba(239, 68, 68, 0.1); color: #dc2626; box-shadow: inset 0 0 0 1px rgba(239,68,68,0.2); }

    @media (max-width: 640px) {
        .hero-grid, .layanan-grid { grid-template-columns: 1fr; }
        .quick-menu { grid-template-columns: 1fr; }
        .quick-card, .layanan-card { min-height: 0; }
    }
</style>

<div class="portal-wrap">
    <div class="portal-title">Portal Desa Sejahtera</div>
    <div class="portal-subtitle">Selamat datang, {{ auth()->user()->name }} <i class="fa-solid fa-handshake"></i></div>

    @php
        $verificationStatus = auth()->user()?->verification_status ?? 'pending';
    @endphp

    @if($verificationStatus === 'pending')
        <div class="portal-empty">Akun Anda sedang menunggu verifikasi admin. Silakan tunggu hingga data disetujui.</div>
    @elseif($verificationStatus === 'rejected')
        <div class="portal-empty">Akun Anda belum disetujui admin. Silakan hubungi admin untuk peninjauan ulang.</div>
    @elseif(!$warga)
        <div class="portal-empty">Data warga belum terhubung ke akun ini. Silakan hubungi admin.</div>
    @endif

    <div class="hero-card" style="margin-top: 14px;">
        <div style="font-size: 14px; opacity: .95;">Ringkasan Pembayaran</div>
        <div style="font-size: 26px; font-weight: 800; line-height: 1.2; margin-top: 3px;">{{ auth()->user()->name }}</div>

        <div class="hero-grid">
            <div class="hero-mini">
                <b>{{ $totalBelumLunas }}</b>
                <small>Tagihan aktif</small>
            </div>
            <div class="hero-mini">
                <b>Rp {{ number_format($totalBulanIni, 0, ',', '.') }}</b>
                <small>Total bulan ini</small>
            </div>
            <div class="hero-mini">
                <b>{{ $pembayaranTerakhirLunas ? \Illuminate\Support\Carbon::parse($pembayaranTerakhirLunas->tanggal_bayar)->translatedFormat("M 'y") : '-' }}</b>
                <small>Terakhir bayar</small>
            </div>
        </div>

        <div class="mini-progress">
            @php
                $progressPaid = $totalTagihan > 0 ? round(($totalLunas / $totalTagihan) * 100) : 0;
            @endphp
            <div style="width: {{ $progressPaid }}%;"></div>
        </div>
    </div>

    @if(isset($riwayatAir) && $riwayatAir->isNotEmpty())
    <div class="section-label" style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-top:20px;">
        <span>Tren Pemakaian Air Anda (6 Bulan Terakhir)</span>
    </div>
    <div style="background:#fff; border:1px solid #dce6f1; border-radius:16px; padding:16px 16px 20px; box-shadow:0 8px 20px rgba(15,23,42,.05); height:220px; position:relative;">
        <canvas id="airChart"></canvas>
    </div>
    @endif



    <div class="section-label">Layanan Pembayaran</div>
    <div class="layanan-grid">
        @foreach($layananPembayaran as $layanan)
            @php
                $item = $layanan['item'];
                $status = ($item->status ?? 'pending') === 'paid' ? 'paid' : 'pending';
                $isDraft = (int) ($item->jumlah ?? 0) <= 0;
                $isDonasiCard = $layanan['nama'] === 'Donasi';
                $hasEventDonasiAktif = isset($eventDonasiAktif) && $eventDonasiAktif->isNotEmpty();
                $hasDonasiBerjalan = $item && ! $isDraft && (($item->status ?? 'pending') === 'pending');
                $isOptionalDonasiEmpty = $isDonasiCard && ! $hasEventDonasiAktif && ! $hasDonasiBerjalan;
            @endphp
            @if($isOptionalDonasiEmpty)
                @continue
            @endif
            <a href="{{ route('user.tagihan', ['jenis' => $layanan['nama']]) }}" class="layanan-card" style="text-decoration:none; color:inherit;">
                <div style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;{{ $layanan['nama'] === 'Air' ? 'background:#eff6ff;color:#3b82f6;' : ($layanan['nama'] === 'Donasi' ? 'background:#fdf2f8;color:#ec4899;' : 'background:#fff7ed;color:#f97316;') }}">
                    @if($layanan['nama'] === 'Air')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-2.486 4.97-6.75 8.783-6.75 12.75 0 3.728 3.022 6.75 6.75 6.75s6.75-3.022 6.75-6.75c0-3.967-4.264-7.78-6.75-12.75z" /></svg>
                    @elseif($layanan['nama'] === 'Donasi')
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:26px;height:26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                    @endif
                </div>
                <h5>Iuran {{ $layanan['nama'] }}</h5>
                <p>{{ $item ? \Illuminate\Support\Carbon::parse($item->tanggal_bayar)->translatedFormat('M Y') : 'Belum ada periode' }}</p>
                <div style="margin-top:6px;font-size:28px;font-weight:800;color:#0f6f63;">@if($isDraft)Belum diisi @else Rp {{ number_format($item->jumlah ?? 0, 0, ',', '.') }} @endif</div>
                @if($item && $item->periode)
                    <div style="margin-top:6px;font-size:12px;color:#52606b;">{{ $item->periode }} @if($item->pemakaian_air !== null) | {{ $item->pemakaian_air }} m3 @endif</div>
                @endif
                @if($item && $item->jatuh_tempo)
                    @php
                        $isOverdue = $item->status !== 'paid' && \Illuminate\Support\Carbon::parse($item->jatuh_tempo)->isPast();
                    @endphp
                    @if($isOverdue)
                        <div style="margin-top:4px;font-size:12px;color:#b91c1c;font-weight:700;">
                            Tempo {{ \Illuminate\Support\Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y') }} | Terlambat
                        </div>
                    @else
                        <div style="margin-top:4px;font-size:12px;color:#52606b;font-weight:400;">
                            Tempo {{ \Illuminate\Support\Carbon::parse($item->jatuh_tempo)->translatedFormat('d M Y') }}
                        </div>
                    @endif
                @endif
                <span class="status-pill {{ $status === 'paid' ? 'pill-paid' : 'pill-pending' }}">{{ $status === 'paid' ? 'Lunas' : ($isDraft ? 'Draft' : 'Belum bayar') }}</span>
            </a>
        @endforeach
    </div>

    <div class="section-label" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <span>Event Donasi Aktif</span>
        <a href="{{ route('user.event-donasi.index') }}" class="section-link" style="text-transform:none;">Lihat semua</a>
    </div>
    <div class="riwayat-list">
        @forelse($eventDonasiAktif as $event)
            @php
                $target = max((int) $event->target_dana, 1);
                $terkumpul = (int) ($event->total_terkumpul ?? 0);
                $progress = min((int) round(($terkumpul / $target) * 100), 100);
            @endphp
            <a href="{{ route('user.event-donasi.show', $event->id) }}" class="riwayat-item" style="text-decoration:none;">
                <div class="left">
                       @if($event->cover_image_url)
                           <img src="{{ $event->cover_image_url }}" alt="{{ $event->nama_event }}" class="event-cover" style="margin-bottom:10px; display:block;">
                       @else
                           <div class="event-cover" style="margin-bottom:10px; background:#e5e7eb; display:flex; align-items:center; justify-content:center; color:#999;">No image</div>
                       @endif
                    <b>{{ $event->nama_event }}</b>
                    <span>{{ $event->tujuan }}</span>
                    <div style="margin-top:6px;width:100%;max-width:180px;height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;">
                        <div style="width: {{ $progress }}%; height:8px; background:#7c3aed;"></div>
                    </div>
                    <span style="display:block;margin-top:4px;">Rp {{ number_format($terkumpul,0,',','.') }} dari Rp {{ number_format($event->target_dana,0,',','.') }}</span>
                </div>
                <div class="right" style="color:#7c3aed;">{{ $progress }}%</div>
            </a>
        @empty
            <div class="riwayat-item">
                <div class="left">
                    <b>Belum ada event donasi aktif</b>
                    <span>Nanti event aktif akan muncul di sini.</span>
                </div>
            </div>
        @endforelse
    </div>

    <div class="section-label" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <span>Riwayat Transaksi</span>
        <a href="{{ route('user.tagihan') }}" class="section-link" style="text-transform:none;">Lihat semua</a>
    </div>
    <div class="riwayat-list">
        @forelse($tagihanTerbaru as $item)
            @php
                $isPaid = ($item->status ?? 'pending') === 'paid';
                $jenisNama = $item->jenisPembayaran->nama ?? '-';
                
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>';
                $bgColor = 'background: linear-gradient(135deg, #94a3b8, #475569); color: #fff; --accent-color: #64748b; --shadow-color: rgba(71,85,105,0.3);';
                if(stripos($jenisNama, 'air') !== false) {
                    $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25c-2.486 4.97-6.75 8.783-6.75 12.75 0 3.728 3.022 6.75 6.75 6.75s6.75-3.022 6.75-6.75c0-3.967-4.264-7.78-6.75-12.75z" /></svg>';
                    $bgColor = 'background: linear-gradient(135deg, #38bdf8, #2563eb); color: #fff; --accent-color: #3b82f6; --shadow-color: rgba(37,99,235,0.4);';
                } elseif(stripos($jenisNama, 'sampah') !== false) {
                    $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>';
                    $bgColor = 'background: linear-gradient(135deg, #fbbf24, #ea580c); color: #fff; --accent-color: #f97316; --shadow-color: rgba(234,88,12,0.4);';
                } elseif(stripos($jenisNama, 'donasi') !== false) {
                    $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:24px;height:24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>';
                    $bgColor = 'background: linear-gradient(135deg, #f472b6, #be185d); color: #fff; --accent-color: #e11d48; --shadow-color: rgba(190,24,93,0.4);';
                }
            @endphp
            <a href="{{ route('user.tagihan', ['jenis' => $jenisNama]) }}" class="transaction-card">
                <div class="transaction-icon" style="{{ $bgColor }}">
                    {!! $icon !!}
                </div>
                <div class="transaction-info">
                    <div class="transaction-title">{{ $jenisNama }}</div>
                    <div class="transaction-date">{{ \Illuminate\Support\Carbon::parse($item->tanggal_bayar)->translatedFormat('d M Y') }} &bull; Periode {{ \Illuminate\Support\Carbon::parse($item->tanggal_bayar)->translatedFormat('M Y') }}</div>
                </div>
                <div class="transaction-amount-wrapper">
                    <span class="transaction-amount {{ $isPaid ? 'paid' : 'unpaid' }}">
                        Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                    </span>
                    <span class="transaction-status {{ $isPaid ? 'paid' : 'unpaid' }}">
                        {{ $isPaid ? 'Lunas' : 'Belum Bayar' }}
                    </span>
                </div>
                <i class="fa-solid fa-chevron-right" style="color: #cbd5e1; margin-left: 16px; font-size: 14px;"></i>
            </a>
        @empty
            <div class="riwayat-item">
                <div class="left">
                    <b>Belum ada data transaksi</b>
                    <span>Data riwayat akan muncul setelah ada pembayaran.</span>
                </div>
            </div>
        @endforelse
    </div>

</div>

@if(isset($riwayatAir) && $riwayatAir->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('airChart').getContext('2d');
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 240);
    gradient.addColorStop(0, 'rgba(29, 95, 184, 0.25)');
    gradient.addColorStop(1, 'rgba(29, 95, 184, 0)');

    const labels = {!! json_encode($riwayatAir->pluck('periode')) !!};
    const dataPemakaian = {!! json_encode($riwayatAir->pluck('pemakaian')) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pemakaian Air (m³)',
                data: dataPemakaian,
                borderColor: '#1d5fb8',
                backgroundColor: gradient,
                borderWidth: 2.5,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#1d5fb8',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#18324d',
                    titleFont: { size: 13 },
                    bodyFont: { size: 14, weight: 'bold' },
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y + ' m³';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: '#f1f5f9' },
                    ticks: { color: '#64748b', font: { size: 11 }, stepSize: 10 }
                },
                x: {
                    border: { display: false },
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 11 } }
                }
            }
        }
    });
});
</script>
@endif

@endsection