<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $baseTitle = $branding['app_name'] ?? config('app.name', 'SIMP-MLD');
            $roleLabel = match (Auth::user()?->role) {
                'admin' => 'Admin',
                'petugas' => 'Petugas',
                'warga' => 'Warga',
                default => 'User',
            };
            $roleTitle = Auth::check()
                ? ($baseTitle . ' - ' . $roleLabel)
                : $baseTitle;
        @endphp
        <title>@yield('title', $roleTitle)</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="manifest" href="/manifest.webmanifest">
        <meta name="theme-color" content="#0ea5a4">
        <link rel="apple-touch-icon" href="/icons/icon-192.svg">
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-slate-50">
            @if(Auth::check())
                @if(Auth::user()->role === 'admin')
                    @include('layouts.admin-sidebar')
                @elseif(Auth::user()->role === 'user' || Auth::user()->role === 'warga')
                    @include('layouts.user-sidebar')
                @endif
            @endif
            <div @if(Auth::check() && in_array(Auth::user()->role, ['admin', 'user', 'warga'])) style="margin-left: 256px;" @endif>
                @include('layouts.navigation')

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    @isset($slot)
                        {{ $slot }}
                    @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </div>
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js').then(function(reg) {
                        console.log('ServiceWorker registered:', reg.scope);
                    }).catch(function(err){
                        console.warn('ServiceWorker registration failed:', err);
                    });
                });
            }
        </script>
    </body>
</html>
