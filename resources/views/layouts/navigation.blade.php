<nav x-data="{ open: false }" style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.3); position: sticky; top: 0; z-index: 50; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="/logo-simp-mld.png" alt="SIMP-MLD Logo" class="block h-9 w-auto object-contain" />
                    </a>
                    <div class="ms-3 hidden lg:block">
                        <div class="text-sm font-semibold text-slate-800">{{ $branding['village_name'] ?? 'Portal Desa' }}</div>
                        <div class="text-xs text-slate-500">{{ $branding['tagline'] ?? 'Sistem Pembayaran Layanan Desa' }}</div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:items-center sm:ms-8" style="gap: 4px;">
                    @php
                        $isDashboard = request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->is('user/dashboard');
                    @endphp
                    <a href="{{ route('dashboard') }}" 
                       style="padding: 8px 14px; border-radius: 9999px; font-weight: 600; font-size: 13px; text-decoration: none; transition: all 0.2s ease; {{ $isDashboard ? 'background: rgba(0,0,0,0.06); color: #0f172a;' : 'color: #475569;' }}"
                       onmouseover="this.style.background='rgba(0,0,0,0.06)'; this.style.color='#0f172a';"
                       onmouseout="this.style.background='{{ $isDashboard ? 'rgba(0,0,0,0.06)' : 'transparent' }}'; this.style.color='{{ $isDashboard ? '#0f172a' : '#475569' }}';">
                        {{ Auth::user()?->role === 'admin' ? 'Dashboard Admin' : 'Dashboard User' }}
                    </a>

                    @if(Auth::user()?->role === 'admin')
                        {{-- Menu admin disembunyikan dari topbar --}}
                    @else
                        {{-- Menu user juga disembunyikan karena sudah dipindah ke user-sidebar --}}
                    @endif
                </div>
            </div>

            <!-- Notification Bell (Admin Only) -->
            @if(Auth::user()?->role === 'admin')
            @php
                $notifPendingVerifikasi = \App\Models\User::where('verification_status', 'pending')->where('role', 'user')->count();
                $notifPembayaranBaru = \App\Models\Pembayaran::where('status', 'pending')->where('jumlah', '>', 0)->whereDate('tanggal_bayar', today())->count();
                $totalNotif = $notifPendingVerifikasi + $notifPembayaranBaru;
            @endphp
            <div style="position:relative;margin-right:8px;" x-data="{ open: false }">
                <button @click="open = !open" style="position:relative;width:38px;height:38px;border-radius:50%;background:rgba(29,78,216,0.08);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.2s;" onmouseover="this.style.background='rgba(29,78,216,0.15)'" onmouseout="this.style.background='rgba(29,78,216,0.08)'">
                    <svg fill="none" stroke="#1d4ed8" stroke-width="2" viewBox="0 0 24 24" style="width:20px;height:20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    @if($totalNotif > 0)
                        <span style="position:absolute;top:4px;right:4px;width:16px;height:16px;background:#ef4444;border-radius:50%;font-size:9px;font-weight:900;color:#fff;display:flex;align-items:center;justify-content:center;border:2px solid #fff;">{{ $totalNotif > 9 ? '9+' : $totalNotif }}</span>
                    @endif
                </button>
                <div x-show="open" @click.outside="open = false" x-transition style="position:absolute;right:0;top:48px;width:300px;background:#fff;border-radius:14px;box-shadow:0 20px 40px rgba(0,0,0,0.12);border:1px solid #e2e8f0;z-index:100;overflow:hidden;">
                    <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;font-weight:800;font-size:13px;color:#0f172a;">🔔 Notifikasi Admin</div>
                    @if($notifPendingVerifikasi > 0)
                        <a href="{{ route('admin.verifikasi-user.index') }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;text-decoration:none;border-bottom:1px solid #f8fafc;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <span style="width:36px;height:36px;background:#eff6ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex:0 0 auto;">👤</span>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#0f172a;">{{ $notifPendingVerifikasi }} User Menunggu Verifikasi</div>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">Klik untuk melihat dan memverifikasi</div>
                            </div>
                        </a>
                    @endif
                    @if($notifPembayaranBaru > 0)
                        <a href="{{ route('pembayaran.wajib') }}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;text-decoration:none;border-bottom:1px solid #f8fafc;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <span style="width:36px;height:36px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex:0 0 auto;">💰</span>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#0f172a;">{{ $notifPembayaranBaru }} Tagihan Pending Hari Ini</div>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">Tagihan belum dibayar hari ini</div>
                            </div>
                        </a>
                    @endif
                    @if($totalNotif === 0)
                        <div style="padding:20px 16px;text-align:center;color:#94a3b8;font-size:13px;">✅ Tidak ada notifikasi baru</div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <span class="ms-2 rounded-full px-2 py-0.5 text-xs font-semibold {{ Auth::user()?->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ Auth::user()?->role === 'admin' ? 'Admin' : 'User' }}
                            </span>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->is('user/dashboard')">
                {{ Auth::user()?->role === 'admin' ? 'Dashboard Admin' : 'Dashboard User' }}
            </x-responsive-nav-link>

            @if(Auth::user()?->role === 'admin')
                {{-- <x-responsive-nav-link :href="route('jenis-pembayaran.index')" :active="request()->routeIs('jenis-pembayaran.*')">
                    {{ __('Jenis Pembayaran') }}
                </x-responsive-nav-link> --}}
                   <x-responsive-nav-link :href="route('pembayaran.wajib')" :active="request()->routeIs('pembayaran.wajib') || (request()->routeIs('pembayaran.*') && request('kategori', 'wajib') !== 'donasi')">
                       {{ __('Pembayaran Rutin') }}
                   </x-responsive-nav-link>
                   <x-responsive-nav-link :href="route('event-donasi.index')" :active="request()->routeIs('event-donasi.*')">
                       {{ __('Event Donasi') }}
                   </x-responsive-nav-link>
                   <x-responsive-nav-link :href="route('event-donasi.laporan')" :active="request()->routeIs('event-donasi.laporan')">
                       {{ __('Laporan Donasi') }}
                   </x-responsive-nav-link>
                   <x-responsive-nav-link :href="route('rekap.bulan')" :active="request()->routeIs('rekap.*') || request()->routeIs('laporan.pdf')">
                       {{ __('Rekap') }}
                   </x-responsive-nav-link>
                   <x-responsive-nav-link :href="route('admin.settings.branding.edit')" :active="request()->routeIs('admin.settings.branding.*')">
                       {{ __('Branding') }}
                   </x-responsive-nav-link>
                   <x-responsive-nav-link :href="route('admin.verifikasi-user.index')" :active="request()->routeIs('admin.verifikasi-user.*')">
                       {{ __('Verifikasi User Baru') }}
                   </x-responsive-nav-link>
            @else
                @php
                    $activeTagihanCountResponsive = $activeTagihanCount ?? 0;
                @endphp

                <x-responsive-nav-link :href="route('user.tagihan')" :active="request()->routeIs('user.tagihan') && request('status') !== 'paid'">
                    <span>{{ __('Tagihan Saya') }}</span>
                    @if($activeTagihanCountResponsive > 0)
                        <span class="ms-2 inline-flex items-center justify-center rounded-full bg-rose-100 text-rose-700 text-xs font-semibold px-2 py-0.5">{{ $activeTagihanCountResponsive }}</span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.tagihan', ['status' => 'paid'])" :active="request()->routeIs('user.tagihan') && request('status') === 'paid'">
                    {{ __('Riwayat') }}
                </x-responsive-nav-link>
                <div class="px-4 py-2">
                    <a href="{{ route('meter.self-report.create') }}" 
                       style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px 16px; background: rgba(20,20,25,0.75); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; font-weight: 700; font-size: 14px; color: white; box-shadow: 0 8px 32px rgba(0,0,0,0.15), inset 0 1px 0 rgba(255,255,255,0.1); transition: transform 0.2s; text-decoration: none;"
                       onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        <svg style="width: 20px; height: 20px; margin-right: 8px; color:#38bdf8;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        {{ __('Lapor OCR Meter') }}
                    </a>
                </div>
                <x-responsive-nav-link :href="route('user.event-donasi.index')" :active="request()->routeIs('user.event-donasi.*')">
                    {{ __('Event Donasi') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('user.event-donasi.history')" :active="request()->routeIs('user.event-donasi.history')">
                    {{ __('Riwayat Donasi') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                <div class="mt-1">
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ Auth::user()?->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                        {{ Auth::user()?->role === 'admin' ? 'Admin' : 'User' }}
                    </span>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
