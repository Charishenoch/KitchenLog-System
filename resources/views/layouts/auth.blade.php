<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KitchenLog - Sistem Manajemen Inventaris Dapur">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KitchenLog')</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="auth-container">
        {{-- Left Brand Panel --}}
        <aside class="auth-brand">
            <div class="brand-logo">
                {{-- Chef hat / kitchen icon --}}
                <svg class="brand-logo-icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M32 8c-4.4 0-8 3.6-8 8 0-.5-3.6-4-8-4-5.5 0-10 4.5-10 10 0 4.8 3.4 8.8 8 9.8V48h36V31.8c4.6-1 8-5 8-9.8 0-5.5-4.5-10-10-10-4.4 0-7.5 3.5-8 4 0-4.4-3.6-8-8-8z" fill="#1b5e32" opacity="0.9"/>
                    <rect x="16" y="48" width="32" height="8" rx="2" fill="#1b5e32"/>
                    <path d="M26 34v10M32 32v12M38 34v10" stroke="#fff" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span class="brand-logo-title">KitchenLog</span>
                <span class="brand-logo-subtitle">Sistem Manajemen Inventaris</span>
            </div>
            <p class="brand-tagline">Kelola inventaris dapur lebih mudah, untuk bisnis restoran yang lebih baik.</p>
        </aside>

        {{-- Right Form Panel --}}
        <main class="auth-form-panel">
            {{-- Top bar --}}
            <div class="auth-topbar">
                @yield('topbar')
            </div>

            {{-- Form Content --}}
            <div class="auth-form-wrapper">
                @yield('content')
            </div>

            {{-- Footer --}}
            <footer class="auth-footer">
                &copy; {{ date('Y') }} KitchenLog. Semua hak dilindungi.
            </footer>
        </main>
    </div>

    @yield('scripts')
</body>
</html>
