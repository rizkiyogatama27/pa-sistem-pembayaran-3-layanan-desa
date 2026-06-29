<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar - {{ config('app.name', 'SIMP-MLD') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ink: #0f172a;
            --primary: #1d4ed8;
            --primary-hover: #1e40af;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; min-height: 100%; font-family: "Inter", "Segoe UI", sans-serif;
            background: #f8fafc; color: var(--ink); overflow: hidden;
        }
        .page {
            height: 100vh; display: grid; grid-template-columns: 1.1fr 0.9fr;
        }
        .left {
            position: relative; overflow: hidden; padding: 12vh 40px 40px; color: #fff;
            background: linear-gradient(95deg, #1c57ad 0%, #196fa0 48%, #178f77 100%);
            display: flex; flex-direction: column; justify-content: flex-start;
        }
        .left::before {
            content: ''; position: absolute; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(56,189,248,0.15) 0%, transparent 70%);
            top: -200px; left: -100px; border-radius: 50%; pointer-events: none;
        }
        .left::after {
            content: ''; position: absolute; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(29,78,216,0.15) 0%, transparent 70%);
            bottom: -150px; right: -100px; border-radius: 50%; pointer-events: none;
        }
        .left-content { position: relative; z-index: 1; max-width: 480px; margin: 0 auto; width: 100%; }
        .brand-logo { height: 64px; width: auto; object-fit: contain; margin-bottom: 24px; background: #ffffff; padding: 10px; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .brand-title { font-size: 34px; font-weight: 900; line-height: 1.2; margin: 0 0 8px; letter-spacing: -0.02em; }
        .brand-subtitle { color: rgba(255,255,255,0.95); font-size: 16px; font-weight: 600; line-height: 1.5; margin-bottom: 40px; }
        .feature-item { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 28px; }
        .feature-icon { width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.08); display: grid; place-items: center; font-size: 18px; border: 1px solid rgba(255,255,255,0.1); color: #38bdf8; flex: 0 0 auto; }
        .feature-item h3 { margin: 0 0 4px; font-size: 15px; font-weight: 700; color: #ffffff; }
        .feature-item p { margin: 0; font-size: 13px; color: rgba(255,255,255,0.85); line-height: 1.5; }
        .left-footer { position: absolute; bottom: 30px; left: 0; width: 100%; text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; }
        
        .right {
            background: #ffffff; display: flex; align-items: flex-start; justify-content: center; padding: 12vh 40px 40px; position: relative; overflow-y: auto;
        }
        .form-card { width: 100%; max-width: 400px; }
        .title { font-size: 28px; font-weight: 800; margin: 0 0 8px; color: #0f172a; }
        .subtitle { font-size: 14px; color: #64748b; margin: 0 0 32px; line-height: 1.5; }
        
        .field { margin-bottom: 20px; }
        .label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 700; color: #334155; }
        .input { width: 100%; height: 46px; border: 1px solid #cbd5e1; border-radius: 12px; padding: 0 16px; font-size: 14px; color: #0f172a; background: #f8fafc; transition: all 0.2s; }
        .input:focus { border-color: var(--primary); background: #ffffff; box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.12); outline: none; }
        
        .input-wrap { position: relative; }
        .icon-btn { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); border: 0; background: transparent; cursor: pointer; color: #64748b; font-size: 16px; width: 30px; height: 30px; display: grid; place-items: center; }
        
        .remember-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; font-size: 13px; }
        .remember { display: flex; align-items: center; gap: 8px; color: #475569; font-weight: 500; cursor: pointer; }
        .remember input { accent-color: var(--primary); width: 16px; height: 16px; }
        .link { color: var(--primary); text-decoration: none; font-weight: 700; transition: color 0.2s; }
        .link:hover { color: var(--primary-hover); }
        
        .primary-btn { width: 100%; height: 46px; border: 0; border-radius: 12px; background: linear-gradient(135deg, #1d4ed8, #0f61a8); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(29, 78, 216, 0.2); }
        .primary-btn:hover { background: linear-gradient(135deg, #1e40af, #0d5392); transform: translateY(-2px); box-shadow: 0 8px 16px rgba(29, 78, 216, 0.25); }
        
        .divider { margin: 28px 0; text-align: center; position: relative; color: #94a3b8; font-size: 13px; font-weight: 600; }
        .divider::before, .divider::after { content: ''; position: absolute; top: 50%; width: calc(50% - 24px); height: 1px; background: #e2e8f0; }
        .divider::before { left: 0; } .divider::after { right: 0; }
        
        .secondary-btn { display: block; width: 100%; padding: 13px; border-radius: 12px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; text-align: center; text-decoration: none; font-size: 14px; font-weight: 700; transition: all 0.2s; }
        .secondary-btn:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
        
        .hint { margin: 28px 0 0; text-align: center; color: #94a3b8; font-size: 12px; line-height: 1.5; }
        .field-error { color: #ef4444; font-size: 12px; margin-top: 6px; font-weight: 500; }
        .alert { padding: 12px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 24px; font-weight: 500; }
        .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        @media (max-width: 1024px) {
            .page { grid-template-columns: 1fr; }
            html, body { overflow: auto; min-height: auto; }
            .left { padding: 60px 24px 80px; text-align: center; align-items: center; justify-content: center; }
            .feature-item { text-align: left; }
            .right { padding: 40px 24px; border-top-left-radius: 30px; border-top-right-radius: 30px; margin-top: -30px; box-shadow: 0 -10px 40px rgba(0,0,0,0.1); justify-content: center; align-items: center; }
            .left-footer { display: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="left">
            <div class="left-content">
                <img src="/logo-simp-mld.png" alt="Portal Desa Logo" class="brand-logo">
                <h1 class="brand-title">Portal Desa</h1>
                <div class="brand-subtitle">Sistem Informasi Manajemen Pembayaran - Multi Layanan Desa<br><br>AIR &bull; SAMPAH &bull; DONASI</div>

                <div class="feature-item">
                    <div class="feature-icon"><i class="fa-solid fa-droplet"></i></div>
                    <div>
                        <h3>Kelola Iuran Air</h3>
                        <p>Pantau tagihan air warga secara real-time dan transparan.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fa-solid fa-trash-can"></i></div>
                    <div>
                        <h3>Iuran Sampah</h3>
                        <p>Manajemen iuran sampah bulanan per kepala keluarga.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
                    <div>
                        <h3>Program Donasi</h3>
                        <p>Donasi transparan untuk pembangunan fasilitas desa.</p>
                    </div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div>
                        <h3>Pembayaran Cepat</h3>
                        <p>Status pembayaran otomatis terupdate setelah transaksi.</p>
                    </div>
                </div>
            </div>
            <div class="left-footer">&copy; {{ date('Y') }} Portal Desa (Sistem Informasi Manajemen Pembayaran - Multi Layanan Desa). Hak Cipta Dilindungi.</div>
        </section>

                <section class="right" style="padding: 6vh 40px 40px; overflow-y: auto;">
            <div class="form-card">
                <h2 class="title">Buat Akun Baru</h2>
                <p class="subtitle" style="margin-bottom: 20px;">Daftar sebagai warga untuk mengakses tagihan, riwayat pembayaran, dan donasi desa.</p>

                <div class="alert alert-success" style="background: #eff6ff; color: #1d4ed8; border-color: #cfe0ff; margin-bottom: 24px; line-height: 1.5;">
                    Setelah mendaftar, akun akan menunggu verifikasi admin sebelum dihubungkan ke data warga.
                </div>

                @if ($errors->any())
                    <div class="alert alert-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="field" style="margin-bottom: 16px;">
                        <label class="label" for="name">Nama Lengkap</label>
                        <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Masukkan nama lengkap">
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="field" style="margin-bottom: 0;">
                            <label class="label" for="nik">NIK</label>
                            <input id="nik" class="input" type="text" name="nik" value="{{ old('nik') }}" required maxlength="16" minlength="16" pattern="[0-9]{16}" placeholder="16 digit NIK">
                            @error('nik') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="field" style="margin-bottom: 0;">
                            <label class="label" for="kk">No. KK</label>
                            <input id="kk" class="input" type="text" name="kk" value="{{ old('kk') }}" required maxlength="16" minlength="16" pattern="[0-9]{16}" placeholder="16 digit KK">
                            @error('kk') <div class="field-error">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="field" style="margin-bottom: 16px;">
                        <label class="label" for="email">Alamat Email</label>
                        <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required placeholder="email@contoh.com">
                        @error('email') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-bottom: 16px;">
                        <label class="label" for="alamat">Alamat Lengkap <span style="color:#ef4444;">*</span></label>
                        <textarea id="alamat" class="input" name="alamat" required placeholder="Masukkan alamat lengkap (Dusun, RT/RW, Desa, Kec.)" style="height: 80px; padding-top: 12px; resize: vertical;">{{ old('alamat') }}</textarea>
                        @error('alamat') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field" style="margin-bottom: 16px;">
                        <label class="label" for="no_hp">Nomor WhatsApp <span style="color:#ef4444;">*</span></label>
                        <input id="no_hp" class="input" type="text" name="no_hp" value="{{ old('no_hp') }}" required placeholder="Contoh: 081234567890" inputmode="numeric">
                        <div style="font-size:12px;color:#64748b;margin-top:5px;">Nomor ini digunakan untuk menerima notifikasi tagihan via WhatsApp.</div>
                        @error('no_hp') <div class="field-error">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div class="field" style="margin-bottom: 0;">
                            <label class="label" for="password">Password</label>
                            <input id="password" class="input" type="password" name="password" required placeholder="Buat password">
                            @error('password') <div class="field-error">{{ $message }}</div> @enderror
                        </div>

                        <div class="field" style="margin-bottom: 0;">
                            <label class="label" for="password_confirmation">Konfirmasi Password</label>
                            <input id="password_confirmation" class="input" type="password" name="password_confirmation" required placeholder="Ulangi password">
                        </div>
                    </div>

                    <button type="submit" class="primary-btn">Daftar Sekarang</button>
                </form>

                <div class="divider">atau</div>

                <a href="{{ route('login') }}" class="secondary-btn">Sudah punya akun? Masuk di sini</a>

                <p class="hint">Dengan mendaftar, Anda menyetujui kebijakan privasi<br>dan persyaratan layanan Portal Desa.</p>
            </div>
        </section>
    </div>
</body>
</html>