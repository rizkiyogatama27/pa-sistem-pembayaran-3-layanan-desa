<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Nama')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4">
                    <p class="text-sm text-amber-900">
                        {{ __('Alamat email Anda belum terverifikasi.') }}

                        <button form="send-verification" class="font-semibold text-sky-700 underline underline-offset-2 hover:text-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                            {{ __('Klik untuk kirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-emerald-600">
                            {{ __('Tautan verifikasi baru sudah dikirim ke email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="nik" :value="__('NIK')" />
            <x-text-input id="nik" name="nik" type="text" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500" :value="old('nik', data_get($user, 'nik'))" maxlength="16" minlength="16" pattern="[0-9]{16}" required />
            <x-input-error class="mt-2" :messages="$errors->get('nik')" />
        </div>

        <div>
            <x-input-label for="kk" :value="__('No. KK')" />
            <x-text-input id="kk" name="kk" type="text" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500" :value="old('kk', data_get($user, 'kk'))" maxlength="16" minlength="16" pattern="[0-9]{16}" required />
            <x-input-error class="mt-2" :messages="$errors->get('kk')" />
        </div>

        <div>
            <x-input-label for="no_hp" :value="__('Nomor WhatsApp (Aktif)')" />
            <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500" :value="old('no_hp', $user->warga->no_hp ?? '')" placeholder="Contoh: 081234567890" />
            <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
            <p class="mt-1 text-sm text-slate-500">Nomor ini digunakan untuk mengirimkan notifikasi pembayaran via WhatsApp.</p>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-500"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
