<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar - {{ config('app.name', 'SIMP-MLD') }}</title>
    <style>
        :root {
            --bg: #eef3f9;
            --blue: #1f5f9e;
            --blue-deep: #0f3d74;
            --teal: #198d7a;
            --ink: #13324b;
            --muted: rgba(255,255,255,0.78);
            --line: #d8e1ec;
            --card: rgba(255,255,255,0.14);
            --shadow: 0 18px 45px rgba(16, 24, 40, 0.10);
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: var(--bg);
            color: var(--ink);
            overflow-x: hidden;
            overflow-y: auto;
        }

        .page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            background: linear-gradient(145deg, #173c74 0%, #1e67a8 45%, #1a8a7d 100%);
            align-items: stretch;
        }

        .left {
            position: relative;
            overflow: hidden;
            padding: 52px 44px 30px;
            color: #fff;
            background: linear-gradient(145deg, #173c74 0%, #1e67a8 45%, #1a8a7d 100%);
        }

        .left::before,
        .left::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
        }

        .left::before {
            width: 260px;
            height: 260px;
            top: -120px;
            right: -80px;
        }

        .left::after {
            width: 180px;
            height: 180px;
            left: -80px;
            bottom: -70px;
        }

        .brand-wrap {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
        }

        .brand-top {
            text-align: center;
            padding-top: 56px;
        }

        .logo-box {
            width: 88px;
            height: 88px;
            margin: 0 auto 18px;
            border-radius: 18px;
            background: rgba(255,255,255,0.96);
            display: grid;
            place-items: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .logo-mark {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(145deg, #0d4f8e, #1d7cc7);
            position: relative;
            clip-path: polygon(50% 0%, 90% 22%, 90% 78%, 50% 100%, 10% 78%, 10% 22%);
            box-shadow: inset 0 0 0 4px rgba(255,255,255,0.12);
        }

        .logo-mark::before,
        .logo-mark::after {
            content: '';
            position: absolute;
            border-radius: 50%;
        }

        .logo-mark::before {
            width: 10px;
            height: 10px;
            left: 14px;
            top: 24px;
            background: #f7b633;
            box-shadow: 18px 0 0 #29d391;
        }

        .logo-mark::after {
            width: 10px;
            height: 10px;
            left: 24px;
            top: 34px;
            background: #2f80ed;
            box-shadow: 18px -10px 0 #1abc9c;
        }

        .brand-name {
            font-size: 30px;
            line-height: 1.1;
            font-weight: 800;
            letter-spacing: 0.03em;
            margin: 0;
        }

        .brand-sub {
            margin-top: 8px;
            letter-spacing: 0.22em;
            font-size: 12px;
            font-weight: 500;
            color: rgba(255,255,255,0.78);
        }

        .feature-list {
            display: grid;
            gap: 18px;
            margin-top: 44px;
            max-width: 460px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .feature-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(255,255,255,0.12);
            display: grid;
            place-items: center;
            font-size: 17px;
            flex: 0 0 auto;
            border: 1px solid rgba(255,255,255,0.15);
        }

        .feature-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 4px;
            color: #fff;
        }

        .feature-text {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            color: rgba(255,255,255,0.76);
        }

        .left-footer {
            position: relative;
            z-index: 1;
            font-size: 12px;
            color: rgba(255,255,255,0.62);
            text-align: center;
            padding-top: 18px;
        }

        .right {
            background: linear-gradient(145deg, #1e67a8 0%, #177d8f 100%);
            display: grid;
            place-items: center;
            padding: 16px 22px;
            color: #fff;
            min-height: 100vh;
            overflow-y: auto;
        }

        .form-card {
            width: 100%;
            max-width: 364px;
            padding: 0;
            padding-bottom: 24px;
            position: relative;
            z-index: 1;
            display: grid;
            gap: 12px;
        }

        .title {
            margin: 0;
            font-size: 26px;
            line-height: 1.15;
            font-weight: 800;
            color: #fff;
        }

        .subtitle {
            margin: 10px 0 0;
            font-size: 12px;
            line-height: 1.65;
            color: rgba(255,255,255,0.82);
        }

        .alert {
            margin-top: 0;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 13px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #cfe0ff;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .field {
            margin-top: 12px;
        }

        .label {
            display: block;
            margin-bottom: 6px;
            font-size: 12px;
            font-weight: 700;
            color: rgba(255,255,255,0.92);
        }

        .input {
            width: 100%;
            height: 38px;
            border: 1px solid #d4dbe5;
            border-radius: 10px;
            background: #fff;
            padding: 0 14px;
            font-size: 12px;
            color: #0f172a;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .input:focus {
            border-color: #2c6ed5;
            box-shadow: 0 0 0 4px rgba(44, 110, 213, 0.12);
        }

        .primary-btn {
            width: 100%;
            margin-top: 10px;
            height: 38px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, #10365f, #2764ab);
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 22px rgba(16, 55, 95, 0.22);
        }

        .secondary-box {
            display: block;
            width: 100%;
            padding: 11px 14px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.12);
            text-align: center;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            margin-top: 0;
        }

        .secondary-box strong { color: #dbeafe; }

        .hint {
            margin: 0;
            text-align: center;
            color: rgba(255,255,255,0.62);
            font-size: 10px;
            line-height: 1.55;
        }

        .field-error {
            margin-top: 8px;
            font-size: 12px;
            color: #fecaca;
        }

        @media (max-width: 1024px) {
            body { overflow-x: hidden; overflow-y: auto; }
            .page { grid-template-columns: 1fr; overflow: visible; }
            .left { padding: 40px 24px 26px; }
            .brand-top { padding-top: 18px; }
            .feature-list { margin: 34px auto 0; }
            .right { padding: 20px 18px 24px; }
            .form-card { max-width: 440px; }
        }

        @media (max-width: 640px) {
            .left { padding: 32px 18px 22px; }
            .brand-name { font-size: 24px; }
            .brand-sub { letter-spacing: 0.16em; }
            .title { font-size: 24px; }
            .feature-list { gap: 14px; }
            .feature-item { gap: 12px; }
            .feature-icon { width: 38px; height: 38px; border-radius: 12px; }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="left">
            <div class="brand-wrap">
                <div>
                    <div class="brand-top">
                        <div class="logo-box">
                            <div class="logo-mark" aria-hidden="true"></div>
                        </div>
                        <h1 class="brand-name">SIMP-MLD</h1>
                        <div class="brand-sub">DESA PANGEAN · AIR · SAMPAH · DONASI</div>
                    </div>

                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">👤</div>
                            <div>
                                <div class="feature-title">Akun Warga Otomatis</div>
                                <p class="feature-text">Daftar sekali, lalu langsung gunakan layanan pembayaran desa.</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">🧾</div>
                            <div>
                                <div class="feature-title">Tagihan Terpusat</div>
                                <p class="feature-text">Kelola data NIK, KK, dan riwayat tagihan di satu portal.</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">🤝</div>
                            <div>
                                <div class="feature-title">Akses Donasi</div>
                                <p class="feature-text">Pantau event donasi aktif dan progres dana secara transparan.</p>
                            </div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">⚡</div>
                            <div>
                                <div class="feature-title">Bayar Lebih Cepat</div>
                                <p class="feature-text">Pembayaran terhubung ke payment gateway dan statusnya otomatis terupdate.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="left-footer">&copy; 2026 SIMP-MLD Desa Pangean</div>
            </div>
        </section>

        <section class="right">
            <div class="form-card">
                <div class="brand-top" style="padding-top:0; margin-bottom: 4px;">
                    <div class="logo-box" style="width:72px;height:72px;margin-bottom:12px;">
                        <div class="logo-mark" aria-hidden="true" style="width:46px;height:46px;"></div>
                    </div>
                    <h1 class="brand-name" style="font-size: 22px;">SIMP-MLD</h1>
                    <div class="brand-sub" style="margin-top: 6px;">PORTAL DAFTAR DESA PANGEAN</div>
                </div>

                <h2 class="title">Buat Akun Baru</h2>
                <p class="subtitle">Daftar sebagai warga untuk mengakses tagihan, riwayat pembayaran, dan donasi desa.</p>
                <div class="alert alert-info" style="margin-top:12px;">
                    Setelah mendaftar, akun akan menunggu verifikasi admin sebelum dihubungkan ke data warga.
                </div>

                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="field">
                        <label class="label" for="name">Nama</label>
                        <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap">
                        @error('name')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="nik">NIK</label>
                        <input id="nik" class="input" type="text" name="nik" value="{{ old('nik') }}" required maxlength="16" minlength="16" pattern="[0-9]{16}" autocomplete="off" placeholder="16 digit NIK">
                        @error('nik')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="kk">No. KK</label>
                        <input id="kk" class="input" type="text" name="kk" value="{{ old('kk') }}" required maxlength="16" minlength="16" pattern="[0-9]{16}" autocomplete="off" placeholder="16 digit No. KK">
                        @error('kk')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="email">Email</label>
                        <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="Masukkan email Anda">
                        @error('email')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="password">Password</label>
                        <input id="password" class="input" type="password" name="password" required autocomplete="new-password" placeholder="Buat password baru">
                        @error('password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="label" for="password_confirmation">Konfirmasi Password</label>
                        <input id="password_confirmation" class="input" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password">
                        @error('password_confirmation')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="primary-btn">Daftar Sekarang</button>
                </form>

                <a href="{{ route('login') }}" class="secondary-box">
                    Sudah punya akun? <strong>Masuk di sini</strong>
                </a>

                <p class="hint">Dengan mendaftar, Anda setuju menggunakan data sesuai kebutuhan layanan desa.</p>
            </div>
        </section>
    </div>
</body>
</html>
