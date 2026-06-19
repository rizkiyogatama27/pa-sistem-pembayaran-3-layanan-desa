<?php
$loginPath = __DIR__ . '/resources/views/auth/login.blade.php';
$registerPath = __DIR__ . '/resources/views/auth/register.blade.php';

$loginHtml = file_get_contents($loginPath);

// Replace title
$loginHtml = str_replace('<title>Masuk - {{ config(\'app.name\', \'SIMP-MLD\') }}</title>', '<title>Daftar - {{ config(\'app.name\', \'SIMP-MLD\') }}</title>', $loginHtml);

// Build new right section
$newRight = <<<HTML
        <section class="right" style="padding: 6vh 40px 40px; overflow-y: auto;">
            <div class="form-card">
                <h2 class="title">Buat Akun Baru</h2>
                <p class="subtitle" style="margin-bottom: 20px;">Daftar sebagai warga untuk mengakses tagihan, riwayat pembayaran, dan donasi desa.</p>

                <div class="alert alert-success" style="background: #eff6ff; color: #1d4ed8; border-color: #cfe0ff; margin-bottom: 24px; line-height: 1.5;">
                    Setelah mendaftar, akun akan menunggu verifikasi admin sebelum dihubungkan ke data warga.
                </div>

                @if (\$errors->any())
                    <div class="alert alert-error">{{ \$errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="field" style="margin-bottom: 16px;">
                        <label class="label" for="name">Nama Lengkap</label>
                        <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Masukkan nama lengkap">
                        @error('name') <div class="field-error">{{ \$message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="field" style="margin-bottom: 0;">
                            <label class="label" for="nik">NIK</label>
                            <input id="nik" class="input" type="text" name="nik" value="{{ old('nik') }}" required maxlength="16" minlength="16" pattern="[0-9]{16}" placeholder="16 digit NIK">
                            @error('nik') <div class="field-error">{{ \$message }}</div> @enderror
                        </div>

                        <div class="field" style="margin-bottom: 0;">
                            <label class="label" for="kk">No. KK</label>
                            <input id="kk" class="input" type="text" name="kk" value="{{ old('kk') }}" required maxlength="16" minlength="16" pattern="[0-9]{16}" placeholder="16 digit KK">
                            @error('kk') <div class="field-error">{{ \$message }}</div> @enderror
                        </div>
                    </div>

                    <div class="field" style="margin-bottom: 16px;">
                        <label class="label" for="email">Alamat Email</label>
                        <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required placeholder="email@contoh.com">
                        @error('email') <div class="field-error">{{ \$message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div class="field" style="margin-bottom: 0;">
                            <label class="label" for="password">Password</label>
                            <input id="password" class="input" type="password" name="password" required placeholder="Buat password">
                            @error('password') <div class="field-error">{{ \$message }}</div> @enderror
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
HTML;

// Replace the <section class="right"> and everything after it
$parts = explode('<section class="right">', $loginHtml);
if (count($parts) == 2) {
    $newHtml = $parts[0] . $newRight;
    file_put_contents($registerPath, $newHtml);
    echo "Successfully updated register.blade.php\n";
} else {
    echo "Failed to parse login.blade.php\n";
}
