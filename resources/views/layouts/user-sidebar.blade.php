<aside class="admin-sidebar flex flex-col fixed top-0 left-0 h-screen w-64 z-30">
    <div class="sidebar-brand">
        <div style="background: linear-gradient(135deg, #1c57ad, #178f77); padding: 7px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(28, 87, 173, 0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
            </svg>
        </div>
        <div>
            <div class="sidebar-brand__title">Portal Desa</div>
            <div class="sidebar-brand__sub">Dashboard Warga</div>
        </div>
    </div>

    @php
        $activeTagihanCount = 0;
        $authUser = Auth::user();

        if ($authUser?->role === 'user' || $authUser?->role === 'warga') {
            $linkedWargaId = $authUser->warga_id;
            if (! $linkedWargaId) {
                $linkedWargaId = \App\Models\Warga::query()->where('nama', $authUser->name)->value('id');
            }
            if ($linkedWargaId) {
                $activeTagihanCount = \App\Models\Pembayaran::query()
                    ->where('warga_id', $linkedWargaId)
                    ->where('status', 'pending')
                    ->count();
            }
        }

        $isDashboard = request()->routeIs('dashboard') || request()->is('user/dashboard');
        $isTagihan = request()->routeIs('user.tagihan') && request('status') !== 'paid';
        $isRiwayat = request()->routeIs('user.tagihan') && request('status') === 'paid';
        $isOcr = request()->routeIs('meter.self-report.create');
        $isEvent = request()->routeIs('user.event-donasi.index');
        $isRiwayatDonasi = request()->routeIs('user.event-donasi.history');
    @endphp

    <div class="sidebar-section">Menu Utama</div>

    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="sidebar-link{{ $isDashboard ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
            Dashboard
        </a>
        
        <div class="sidebar-section">Pembayaran</div>
        <a href="{{ route('user.tagihan') }}" class="sidebar-link{{ $isTagihan ? ' active' : '' }}" style="display: flex; justify-content: space-between; align-items: center; padding-right: 18px;">
            <div style="display: flex; align-items: center;">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                Tagihan Saya
            </div>
            @if($activeTagihanCount > 0)
                <span style="background: #ef4444; color: white; border-radius: 999px; padding: 2px 7px; font-size: 11px; font-weight: 800; box-shadow: 0 2px 4px rgba(239,68,68,0.3);">{{ $activeTagihanCount }}</span>
            @endif
        </a>
        <a href="{{ route('user.tagihan', ['status' => 'paid']) }}" class="sidebar-link{{ $isRiwayat ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
            Riwayat Pembayaran
        </a>
        <a href="{{ route('meter.self-report.create') }}" class="sidebar-link{{ $isOcr ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #38bdf8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Lapor Meter (OCR)
        </a>

        <div class="sidebar-section">Sosial & Donasi</div>
        <a href="{{ route('user.event-donasi.index') }}" class="sidebar-link{{ $isEvent ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>
            Event Donasi
        </a>
        <a href="{{ route('user.event-donasi.history') }}" class="sidebar-link{{ $isRiwayatDonasi ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6"/><path d="M7 17v-2a2 2 0 012-2h6a2 2 0 012 2v2"/></svg>
            Riwayat Donasi
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-footer__badge" style="background: rgba(255,255,255,0.15);">Portal Warga</div>
        <div class="sidebar-footer__text">Layanan digital desa mandiri untuk Anda.</div>
    </div>
</aside>

<style>
.admin-sidebar {
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    border-right: 1px solid rgba(255,255,255,0.08);
    box-shadow: 10px 0 30px rgba(15, 23, 42, 0.18);
    color: #e2e8f0;
}
.sidebar-brand {
    height: 88px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 20px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.sidebar-brand__title {
    font-size: 18px;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.1;
}
.sidebar-brand__sub {
    font-size: 11px;
    font-weight: 600;
    color: rgba(255,255,255,0.54);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-top: 4px;
}
.sidebar-section {
    padding: 24px 20px 8px;
    font-size: 11px;
    font-weight: 800;
    color: rgba(255,255,255,0.42);
    text-transform: uppercase;
    letter-spacing: 0.1em;
}
.sidebar-nav {
    display: flex;
    flex-direction: column;
    padding: 0 10px;
    flex: 1;
    overflow-y: auto;
}
.sidebar-nav::-webkit-scrollbar { width: 4px; }
.sidebar-nav::-webkit-scrollbar-track { background: transparent; }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.sidebar-link {
    display: flex;
    align-items: center;
    padding: 12px 14px;
    border-radius: 14px;
    color: rgba(255,255,255,0.82);
    font-weight: 700;
    text-decoration: none;
    transition: background 0.2s, color 0.2s, transform 0.2s, box-shadow 0.2s;
    position: relative;
    font-size: 14px;
    margin: 0 4px;
}
.sidebar-link.active, .sidebar-link:hover {
    background: linear-gradient(135deg, rgba(255,255,255,0.12), rgba(255,255,255,0.06));
    color: #ffffff;
    box-shadow: 0 10px 18px rgba(0, 0, 0, 0.12);
    transform: translateX(1px);
}
.sidebar-icon {
    width: 22px;
    height: 22px;
    margin-right: 14px;
    color: rgba(255,255,255,0.62);
    flex: 0 0 auto;
}
.sidebar-link.active .sidebar-icon,
.sidebar-link:hover .sidebar-icon {
    color: #ffffff;
}
.sidebar-footer {
    margin-top: auto;
    padding: 16px 18px 18px;
    border-top: 1px solid rgba(255,255,255,0.08);
}
.sidebar-footer__badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 6px 10px;
    background: rgba(255,255,255,0.10);
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
}
.sidebar-footer__text {
    margin-top: 8px;
    font-size: 12px;
    line-height: 1.5;
    color: rgba(255,255,255,0.66);
}
</style>
