<x-app-layout>
    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <style>
                .profile-shell { max-width: 1120px; margin: 0 auto; padding: 0 0 44px; }
                .profile-grid { display: grid; gap: 16px; }
                .profile-card { background:#fff; border:1px solid #dce6f1; border-radius:18px; overflow:hidden; box-shadow:0 10px 22px rgba(15,23,42,.05); }
                .profile-card__head { padding: 16px 20px; border-bottom:1px solid #e5edf5; background: linear-gradient(180deg, #fbfdff, #fff); }
                .profile-card__eyebrow { font-size:11px; font-weight:900; letter-spacing:.16em; text-transform:uppercase; color:#215d90; }
                .profile-card__desc { margin-top:6px; font-size:14px; color:#64748b; }
                .profile-card__body { padding: 20px; }
                .profile-hero { overflow:hidden; border-radius:22px; border:1px solid #bfd8ef; background: linear-gradient(135deg, #185ea8 0%, #1e62a1 42%, #188f78 100%); color:#fff; padding:22px; box-shadow:0 18px 30px rgba(24,94,168,.16); }
                .profile-hero__meta { font-size:11px; font-weight:900; letter-spacing:.24em; text-transform:uppercase; color:rgba(255,255,255,.78); }
                .profile-hero__title { margin-top:6px; font-size:28px; line-height:1.1; font-weight:900; }
                .profile-hero__desc { margin-top:8px; max-width:760px; font-size:14px; line-height:1.6; color:rgba(255,255,255,.82); }
                .profile-hero__badge { display:inline-flex; margin-top:14px; align-items:center; border-radius:999px; border:1px solid rgba(255,255,255,.18); background:rgba(255,255,255,.12); padding:8px 14px; font-size:12px; font-weight:700; backdrop-filter:blur(8px); }
                .profile-card--danger .profile-card__head { background: linear-gradient(180deg, #fff7f8, #fff); }
                .profile-card--danger .profile-card__eyebrow { color: #be123c; }
            </style>

            <div class="profile-shell">
                <div class="profile-hero">
                    <div class="profile-hero__meta">Akun Anda</div>
                    <div class="profile-hero__title">{{ __('Profil Akun') }}</div>
                    <p class="profile-hero__desc">Kelola identitas akun, ubah password, dan atur keamanan dari satu tempat yang seragam dengan tampilan dashboard.</p>
                    <div class="profile-hero__badge">Pengaturan akun terpusat</div>
                </div>

                <div class="profile-grid">
                    <div class="profile-card">
                        <div class="profile-card__head">
                            <div class="profile-card__eyebrow">Informasi akun</div>
                            <div class="profile-card__desc">Perbarui data dasar akun Anda.</div>
                        </div>
                        <div class="profile-card__body">
                            <div class="max-w-xl">
                                @include('profile.partials.update-profile-information-form')
                            </div>
                        </div>
                    </div>

                    <div class="profile-card">
                        <div class="profile-card__head">
                            <div class="profile-card__eyebrow">Keamanan</div>
                            <div class="profile-card__desc">Ganti password secara berkala untuk menjaga akun tetap aman.</div>
                        </div>
                        <div class="profile-card__body">
                            <div class="max-w-xl">
                                @include('profile.partials.update-password-form')
                            </div>
                        </div>
                    </div>

                    <div class="profile-card profile-card--danger">
                        <div class="profile-card__head">
                            <div class="profile-card__eyebrow">Zona bahaya</div>
                            <div class="profile-card__desc">Penghapusan akun bersifat permanen dan tidak bisa dibatalkan.</div>
                        </div>
                        <div class="profile-card__body">
                            <div class="max-w-xl">
                                @include('profile.partials.delete-user-form')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
