<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Layanan Pembayaran') }}</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
                :root {
                        --bg: #eef4fb;
                        --ink: #0f172a;
                        --muted: #5f6f87;
                        --line: #d8e3f0;
                        --card: #ffffff;
                        --primary: #1f57c3;
                        --primary-2: #188f78;
                        --deep: #0d1a34;
                }
                * { box-sizing: border-box; }
                body {
                        margin: 0;
                        font-family: "Segoe UI", Arial, sans-serif;
                        background: radial-gradient(circle at top left, rgba(31, 87, 195, .07), transparent 32%), var(--bg);
                        color: var(--ink);
                }
                .container { max-width: 1180px; margin: 0 auto; padding: 14px 18px 26px; }
                .topbar {
                        position: relative;
                        display: grid;
                        grid-template-columns: auto 1fr auto;
                        align-items: center;
                        gap: 10px;
                        background: #fff;
                        border: 1px solid var(--line);
                        border-radius: 16px;
                        padding: 10px 14px;
                        box-shadow: 0 10px 25px rgba(10, 30, 70, .06);
                }
                .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; justify-self: start; }
                .logo {
                        width: 34px;
                        height: 34px;
                        border-radius: 8px;
                        background: linear-gradient(135deg, #1d4ed8, #0f766e);
                        color: #fff;
                        display: grid;
                        place-items: center;
                        font-size: 13px;
                        font-weight: 900;
                }
                .menu {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 8px;
                        align-items: center;
                        justify-content: center;
                        position: absolute;
                        left: 50%;
                        transform: translateX(-50%);
                        width: max-content;
                }
                .menu a {
                        text-decoration: none;
                        color: #334155;
                        padding: 10px 20px;
                        font-size: 14px;
                        font-weight: 800;
                        border-radius: 999px;
                        background: #f1f5f9;
                        border: 1px solid #e2e8f0;
                        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                }
                .menu a:hover {
                        background: #1d4ed8;
                        color: #ffffff;
                        border-color: #1d4ed8;
                        transform: translateY(-2px);
                        box-shadow: 0 6px 14px rgba(29, 78, 216, 0.3);
                }
                .btn {
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        text-decoration: none;
                        border: 0;
                        border-radius: 8px;
                        padding: 9px 14px;
                        font-weight: 700;
                        cursor: pointer;
                }
                .btn-primary { background: linear-gradient(135deg, #1d4ed8, #0f61a8); color: #fff; }
                .btn-light { background: #fff; color: #0f172a; border: 1px solid #dbe6f3; }

                .hero {
                        margin-top: 10px;
                        border-radius: 20px;
                        overflow: hidden;
                        background: linear-gradient(95deg, #1c57ad 0%, #196fa0 48%, #178f77 100%);
                        color: #fff;
                        text-align: center;
                        padding: 46px 22px;
                        position: relative;
                }
                .hero h1 { margin: 8px 0 8px; font-size: clamp(30px, 4vw, 44px); line-height: 1.05; }
                .hero p { margin: 0 auto; max-width: 760px; color: rgba(255,255,255,.92); }
                .hero .pill {
                        display: inline-block;
                        border: 1px solid rgba(255,255,255,.25);
                        background: rgba(255,255,255,.12);
                        padding: 5px 10px;
                        border-radius: 999px;
                        font-size: 11px;
                        font-weight: 800;
                        letter-spacing: .04em;
                        text-transform: uppercase;
                }
                .hero-actions { margin-top: 16px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }

                .stats {
                        margin-top: 14px;
                        display: grid;
                        grid-template-columns: repeat(3, minmax(0,1fr));
                        gap: 12px;
                }
                .stat {
                        background: #fff;
                        border: 1px solid var(--line);
                        border-radius: 12px;
                        padding: 12px;
                        text-align: center;
                }
                .stat .v { font-size: 26px; font-weight: 900; color: #0f172a; }
                .stat .k { color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }

                .video-wrap {
                        margin-top: 14px;
                        position: relative;
                        border-radius: 14px;
                        overflow: hidden;
                        background: #000;
                        padding-top: 56.25%;
                }
                #center-video {
                        position: absolute;
                        top: -1px;
                        left: -1px;
                        width: calc(100% + 2px);
                        height: calc(100% + 2px);
                        object-fit: cover;
                        background: #000;
                }
                #center-mute {
                        position: absolute;
                        right: 10px;
                        bottom: 10px;
                        z-index: 2;
                        border: 0;
                        border-radius: 10px;
                        background: rgba(0,0,0,.5);
                        color: #fff;
                        padding: 8px 10px;
                        font-weight: 700;
                        cursor: pointer;
                }

                .section {
                        margin-top: 18px;
                        background: #fff;
                        border: 1px solid var(--line);
                        border-radius: 14px;
                        padding: 16px;
                }
                .eyebrow { color: #64748b; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
                .title { margin: 4px 0 12px; font-size: 24px; font-weight: 900; }

                .feature-grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                        gap: 16px;
                }
                .feature {
                        background: rgba(255, 255, 255, 0.6);
                        backdrop-filter: blur(12px);
                        -webkit-backdrop-filter: blur(12px);
                        border: 1px solid rgba(15, 23, 42, 0.08);
                        border-radius: 20px;
                        padding: 24px;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
                        display: flex;
                        flex-direction: column;
                        align-items: flex-start;
                        gap: 14px;
                }
                .feature:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 16px 32px rgba(15, 23, 42, 0.08);
                        border-color: rgba(15, 23, 42, 0.15);
                        background: #fff;
                }
                .feature-icon {
                        width: 52px;
                        height: 52px;
                        border-radius: 14px;
                        display: grid;
                        place-items: center;
                        color: white;
                        font-size: 22px;
                        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
                }
                .feature h3 { margin: 0; font-size: 19px; font-weight: 800; color: #0f172a; }
                .feature p { margin: 0; color: #475569; line-height: 1.5; font-size: 15px; opacity: 0.9; }
                .guide-grid {
                        display: grid;
                        grid-template-columns: repeat(3, minmax(0,1fr));
                        gap: 10px;
                }
                .step {
                        border: 1px solid #e2ebf8;
                        border-radius: 12px;
                        padding: 12px;
                        background: #fcfdff;
                }
                .step .n {
                        width: 28px;
                        height: 28px;
                        border-radius: 999px;
                        display: grid;
                        place-items: center;
                        background: #1f57c3;
                        color: #fff;
                        font-weight: 900;
                        margin-bottom: 8px;
                }

                .event-card {
                        border: 1px solid #dbe6f3;
                        border-radius: 12px;
                        overflow: hidden;
                        max-width: 380px;
                }
                .event-cover { height: 140px; background: linear-gradient(135deg, #1d4ed8, #0f766e); color:#fff; display:grid; place-items:center; font-weight:800; }
                .event-body { padding: 12px; }
                .progress {
                        margin: 8px 0;
                        height: 8px;
                        border-radius: 999px;
                        background: #e2e8f0;
                        overflow: hidden;
                }
                .progress > span {
                        display: block;
                        height: 100%;
                        width: 0;
                        background: linear-gradient(90deg, #22c55e, #16a34a);
                }

                .platform {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 20px;
                        flex-wrap: wrap;
                        background: #ffffff;
                        border: 1px solid #e2e8f0;
                        border-radius: 20px;
                        padding: 40px;
                        color: #0f172a;
                        position: relative;
                        overflow: hidden;
                        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
                        margin-top: 32px;
                }
                .platform .eyebrow { color: #1d4ed8 !important; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; font-size: 12px; margin-bottom: 8px; display: block; }
                .platform .title { color: #0f172a !important; font-size: 28px; font-weight: 900; }
                .platform p { color: #475569 !important; font-size: 16px; margin: 0; }

                .footer {
                        margin-top: 32px;
                        border-radius: 24px;
                        background: #0f172a;
                        color: #94a3b8;
                        padding: 48px 40px 24px;
                        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                }
                .footer-grid {
                        display: grid;
                        grid-template-columns: 2fr 1fr 1fr;
                        gap: 32px;
                        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                        padding-bottom: 32px;
                        margin-bottom: 24px;
                }
                .footer h3, .footer h4 { color: #f8fafc !important; font-weight: 800; }
                .footer a { 
                        color: #cbd5e1; 
                        text-decoration: none; 
                        display: inline-block; 
                        margin: 4px 0; 
                        transition: color 0.2s;
                }
                .footer a:hover { color: #38bdf8; }
                .copyright { 
                        display: flex; 
                        justify-content: space-between; 
                        align-items: center; 
                        font-size: 14px; 
                        color: #64748b; 
                        flex-wrap: wrap;
                        gap: 10px;
                }
                .donation-head { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
                .donation-grid { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 14px; }
                .donation-more { display:inline-flex; align-items:center; justify-content:center; padding: 8px 12px; border-radius: 999px; background:#fff; color:#0f172a; text-decoration:none; font-weight:800; border:1px solid #dbe6f3; }

                @media (max-width: 900px) {
                        .stats { grid-template-columns: 1fr; }
                        .feature-grid { grid-template-columns: 1fr; }
                        .guide-grid { grid-template-columns: 1fr; }
                        .footer-grid { grid-template-columns: 1fr; }
                        .menu { display: none; }
                        .donation-grid { grid-template-columns: 1fr; }
                }
        </style>
</head>
<body>
        <div class="container">
                <header class="topbar">
                        <div class="brand">
                                <img src="/logo-simp-mld.png" alt="SIMP-MLD Logo" style="height:36px;width:auto;object-fit:contain;">
                                <div style="display:flex;flex-direction:column;line-height:1.2;">
                                        <span>Portal Desa</span>
                                        <span style="font-size:11px;color:#64748b;font-weight:600;">Sistem Informasi Manajemen Pembayaran Desa</span>
                                </div>
                        </div>
                        <nav class="menu">
                                <a href="#tata-cara-gambar">Tata Cara</a>
                                <a href="#donasi">Donasi</a>
                                <a href="/login">Cek Tagihan</a>
                                <a href="#tutorial">Video Tutorial</a>
                        </nav>
                                <a href="{{ route('login') }}" class="btn btn-primary" style="justify-self:end;">Masuk</a>
                </header>

                <section class="hero">
                        <span class="pill">Layanan Digital Desa</span>
                        <h1>Kelola Iuran Desa<br>Lebih Mudah & Transparan</h1>
                        <p>Pantau tagihan, kelola pembayaran, dan verifikasi transaksi warga secara real-time dalam satu portal terpadu.</p>
                        <div class="hero-actions">
                                <a href="{{ route('login') }}" class="btn btn-light">Cek Tagihan Saya</a>
                                <a href="#tata-cara-gambar" class="btn btn-light">Pelajari Lebih Lanjut</a>
                                <a href="#tutorial" class="btn btn-light">Tonton Tutorial</a>
                        </div>
                </section>

                <section id="tutorial" class="video-wrap">
                                                <video id="center-video" poster="{{ asset('images/cara-pembayaran.svg') }}" autoplay muted loop playsinline controls>
                                                        <source src="{{ asset('videos/cara-pembayaran.mp4') }}" type="video/mp4">
                                                        <track src="{{ asset('videos/cara-pembayaran.srt') }}" kind="subtitles" srclang="id" label="Indonesia" default>
                                                        Your browser does not support the video tag. <a href="{{ asset('videos/cara-pembayaran.mp4') }}">Download video</a>
                                                </video>
                        <button id="center-mute" aria-pressed="false" title="Unmute">Unmute</button>
                </section>

                <section class="section" id="tata-cara-gambar">
                        <div class="eyebrow">Panduan</div>
                        <h2 class="title">Tata Cara Pembayaran</h2>
                        <img src="{{ asset('images/tata-cara-pembayaran.png') }}" alt="Tata Cara Pembayaran" style="width:100%;display:block;border-radius:12px;border:1px solid var(--line);" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="display:none;color:var(--muted);margin-top:8px;">Gambar tidak ditemukan: <strong>public/images/tata-cara-pembayaran.png</strong></div>
                </section>



                <section id="donasi" class="section">
                        <div class="donation-head">
                                <div>
                                        <div class="eyebrow">Program Aktif</div>
                                        <h2 class="title">Event Donasi Aktif</h2>
                                </div>
                                <a href="{{ route('login') }}" class="donation-more">Lihat Semua</a>
                        </div>
                        <div class="donation-grid">
                                @forelse($activeEvents->take(3) as $event)
                                        @php
                                                $eventName = $event?->nama_event ?? 'Renovasi Masjid';
                                                $eventTujuan = $event?->tujuan ?? 'renovasi';
                                                $target = max((int)($event?->target_dana ?? 100000), 1);
                                                $terkumpul = (int)($event?->total_terkumpul ?? 10000);
                                                $progress = min((int)round(($terkumpul / $target) * 100), 100);
                                                $eventUrl = route('event-donasi.kontribusi.public', $event->id);
                                                $eventCoverUrl = $event?->cover_image_url;
                                        @endphp
                                        <article class="event-card" style="max-width:none;">
                                                <div class="event-cover" @if(!empty($eventCoverUrl)) style="background-image: linear-gradient(180deg, rgba(15,23,42,.10), rgba(15,23,42,.42)), url('{{ $eventCoverUrl }}'); background-size: cover; background-position: center;" @endif>
                                                        @if(empty($eventCoverUrl))
                                                                {{ strtoupper($eventName) }}
                                                        @endif
                                                </div>
                                                <div class="event-body">
                                                        <h3 style="margin:0 0 4px">{{ $eventName }}</h3>
                                                        <p style="margin:0;color:var(--muted)">{{ $eventTujuan }}</p>
                                                        <div style="margin-top:8px;font-weight:700">Terkumpul Rp {{ number_format($terkumpul,0,',','.') }}</div>
                                                        <div class="progress"><span class="progress-fill" data-progress="{{ $progress }}"></span></div>
                                                        <div style="font-size:13px;color:var(--muted);margin-bottom:8px">{{ $progress }}%</div>
                                                        <a href="{{ $eventUrl }}" class="btn btn-primary">Donasi Sekarang</a>
                                                </div>
                                        </article>
                                @empty
                                        <div class="event-card" style="max-width:none; padding:16px; color:var(--muted);">
                                                Belum ada event donasi aktif.
                                        </div>
                                @endforelse
                        </div>
                </section>

                <section class="platform">
                        <div style="position:relative;z-index:1;">
                                <span class="eyebrow">Platform</span>
                                <h2 class="title" style="margin-bottom:8px;">Semua yang dibutuhkan untuk portal desa</h2>
                                <p>Kelola keuangan desa, tagihan, dan donasi dalam satu platform terintegrasi.</p>
                        </div>
                        <a href="{{ route('login') }}" class="btn btn-primary" style="position:relative;z-index:1;font-weight:800;padding:12px 24px;font-size:16px;box-shadow:0 8px 16px rgba(29,78,216,0.2);">Cek Tagihan Saya</a>
                </section>

                <footer id="footer" class="footer">
                        <div class="footer-grid">
                                <div>
                                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                                                <img src="/logo-simp-mld.png" alt="SIMP-MLD Logo" style="height:44px;width:auto;object-fit:contain;background:#fff;padding:4px;border-radius:8px;">
                                                <div style="display:flex;flex-direction:column;line-height:1.2;">
                                                        <h3 style="margin:0;font-size:20px;color:#fff;">Portal Desa</h3>
                                                        <span style="font-size:12px;color:#94a3b8;font-weight:600;">Sistem Informasi Manajemen Pembayaran</span>
                                                </div>
                                        </div>
                                        <p style="margin:0;line-height:1.6;max-width:300px;">Solusi pembayaran terpadu untuk desa. Memudahkan warga dalam membayar tagihan dan berdonasi secara aman dan transparan.</p>
                                </div>
                                <div>
                                        <h4 style="margin:0 0 16px;font-size:16px;">Navigasi</h4>
                                        <div style="display:flex;flex-direction:column;gap:8px;">
                                                <a href="#tata-cara-gambar">Tata Cara</a>
                                                <a href="#donasi">Program Donasi</a>
                                        </div>
                                </div>
                                <div>
                                        <h4 style="margin:0 0 16px;font-size:16px;">Bantuan</h4>
                                        <p style="margin:0 0 12px;line-height:1.6;">Hubungi admin untuk bantuan lebih lanjut.</p>
                                        <div style="display:flex;align-items:center;gap:10px;font-weight:700;color:#f8fafc;background:rgba(255,255,255,0.05);padding:10px 14px;border-radius:12px;width:fit-content;border:1px solid rgba(255,255,255,0.1);">
                                                <i class="fa-brands fa-whatsapp" style="font-size:22px;color:#22c55e;"></i>
                                                085230236462
                                        </div>
                                </div>
                        </div>
                        <div class="copyright">
                                <div>© {{ date('Y') }} Portal Desa (Sistem Informasi Manajemen Pembayaran - Multi Layanan Desa). Hak Cipta Dilindungi.</div>
                                <div>Versi sistem 1.0</div>
                        </div>
                </footer>
        </div>

        <script>
                document.addEventListener('DOMContentLoaded', function () {
                        if ('serviceWorker' in navigator) {
                                navigator.serviceWorker.getRegistrations().then(function (regs) {
                                        regs.forEach(function (reg) { reg.unregister(); });
                                });
                        }
                        if (window.caches && window.isSecureContext) {
                                caches.keys().then(function (keys) {
                                        keys.forEach(function (key) { caches.delete(key); });
                                });
                        }

                        var v = document.getElementById('center-video');
                        var b = document.getElementById('center-mute');
                        if (v) { v.play().catch(function(){}); }
                        var fills = document.querySelectorAll('.progress-fill');
                        fills.forEach(function (el) {
                                var p = Number(el.getAttribute('data-progress') || 0);
                                el.style.width = Math.max(0, Math.min(100, p)) + '%';
                        });

                        if (v && b) {
                                b.addEventListener('click', function () {
                                        v.muted = !v.muted;
                                        b.textContent = v.muted ? 'Unmute' : 'Mute';
                                        b.setAttribute('aria-pressed', String(!v.muted));
                                        b.title = v.muted ? 'Unmute' : 'Mute';
                                });
                        }
                });
        </script>
</body>
</html>
