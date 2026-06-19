<aside class="admin-sidebar hidden lg:flex flex-col fixed top-0 left-0 h-screen w-64 z-30">
    <div class="sidebar-brand">
        <div style="background: linear-gradient(135deg, #1c57ad, #178f77); padding: 7px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(28, 87, 173, 0.3);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
            </svg>
        </div>
        <div>
            <div class="sidebar-brand__title">Portal Desa</div>
            <div class="sidebar-brand__sub">Admin Dashboard</div>
        </div>
    </div>

    <div style="padding: 20px 14px 0; width: 100%; box-sizing: border-box;">
        <div class="sidebar-search">
            <span class="sidebar-search__icon">⌕</span>
            <input type="text" placeholder="Cari menu" class="sidebar-search__input" />
        </div>
    </div>

    <div class="sidebar-section">Menu Utama</div>

    <nav class="sidebar-nav">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-link{{ request()->routeIs('admin.dashboard') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="{{ route('warga.index') }}" class="sidebar-link{{ request()->routeIs('warga.*') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 20h5v-1a4 4 0 00-4-4h-1m-4 5H2v-1a4 4 0 014-4h3m5-4a4 4 0 100-8 4 4 0 000 8zm-8 0a3 3 0 100-6 3 3 0 000 6z"/></svg>
            Data Warga
        </a>
        <a href="{{ route('admin.user-warga.index') }}" class="sidebar-link{{ request()->routeIs('admin.user-warga.*') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6M9 8h6"/><path d="M4 6h16v12H4z"/></svg>
            Verifikasi User
        </a>
        <a href="{{ route('jenis-pembayaran.index') }}" class="sidebar-link{{ request()->routeIs('jenis-pembayaran.*') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 1.343-3 3 0 2.5 3 5 3 5s3-2.5 3-5c0-1.657-1.343-3-3-3z"/><circle cx="12" cy="8" r="3"/></svg>
            Jenis Pembayaran
        </a>
        <div class="sidebar-section">Pembayaran</div>
        <a href="{{ route('pembayaran.wajib') }}" class="sidebar-link{{ request()->routeIs('pembayaran.wajib') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="8" width="18" height="13" rx="2"/><path d="M16 3h-1a2 2 0 00-2 2v3"/></svg>
            Pembayaran Rutin
        </a>
        <a href="{{ route('admin.meter.verify.index') }}" class="sidebar-link{{ request()->routeIs('admin.meter.verify.*') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            Verifikasi Meter
        </a>
        <a href="{{ route('event-donasi.index') }}" class="sidebar-link{{ request()->routeIs('event-donasi.*') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 1.343-3 3 0 2.5 3 5 3 5s3-2.5 3-5c0-1.657-1.343-3-3-3z"/><circle cx="12" cy="8" r="3"/></svg>
            Event Donasi
        </a>
        <div class="sidebar-section">Laporan</div>
        <a href="{{ route('event-donasi.laporan') }}" class="sidebar-link{{ request()->routeIs('event-donasi.laporan') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6"/><path d="M7 17v-2a2 2 0 012-2h6a2 2 0 012 2v2"/></svg>
            Laporan Donasi
        </a>
        <a href="{{ route('rekap.bulan') }}" class="sidebar-link{{ request()->routeIs('rekap.*') || request()->routeIs('laporan.pdf') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4"/></svg>
            Lihat Rekap
        </a>
        <a href="{{ route('rekap.tunggakan') }}" class="sidebar-link{{ request()->routeIs('rekap.tunggakan') ? ' active' : '' }}">
            <svg class="sidebar-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
            Tunggakan Air
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-footer__badge">Admin Desa</div>
        <div class="sidebar-footer__text">Pusat kendali administratif untuk memudahkan pengelolaan portal digital desa Anda.</div>
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
.sidebar-brand__logo {
    width: 34px;
    height: 34px;
    object-fit: contain;
}
.sidebar-brand__title {
    font-size: 18px;
    font-weight: 900;
    color: #ffffff;
    line-height: 1.1;
}
.sidebar-brand__sub {
    font-size: 12px;
    color: rgba(255,255,255,0.68);
    margin-top: 2px;
}
.sidebar-search {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(255,255,255,0.06);
    border-radius: 14px;
    padding: 0 14px;
    height: 44px;
    width: 100%;
    box-sizing: border-box;
}
.sidebar-search__icon {
    color: rgba(255,255,255,0.5);
    font-size: 14px;
}
.sidebar-search__input {
    border: 0;
    background: transparent;
    width: 100%;
    font-size: 14px;
    color: #ffffff;
    outline: none;
}
.sidebar-section {
    padding: 18px 20px 8px;
    font-size: 11px;
    font-weight: 900;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: rgba(255,255,255,0.38);
}
.sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 0 10px 14px;
    flex: 1;
    overflow-y: auto;
}
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}
.sidebar-nav::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}
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
