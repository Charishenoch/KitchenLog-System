@extends('layouts.auth')

@section('title', 'Masuk - KitchenLog')

@section('topbar')
    Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang &rarr;</a>
@endsection

@section('content')
    <h1>Selamat Datang Kembali!</h1>
    <p class="auth-desc">Masuk ke akun KitchenLog Anda untuk melanjutkan pengelolaan inventaris.</p>

    @if ($errors->any())
        <div class="alert-error" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:.9rem;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form id="loginForm" method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email / Username --}}
        <div class="form-group">
            <label for="login-email">Email atau Username</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M2 7l10 7 10-7"/></svg>
                </span>
                <input type="text" id="login-email" name="email" placeholder="contoh@email.com atau username" autocomplete="username" value="{{ old('email') }}">
            </div>
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label for="login-password">Password</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 1 1 8 0v4"/></svg>
                </span>
                <input type="password" id="login-password" name="password" placeholder="Masukkan password" autocomplete="current-password">
                <button type="button" class="toggle-password" onclick="togglePassword('login-password', this)" aria-label="Toggle password visibility">
                    <svg class="eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>

        {{-- Remember / Forgot --}}
        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember" id="remember">
                <span class="checkmark">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                Ingat saya
            </label>
            <a href="#" class="forgot-link">Lupa password?</a>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary" id="btn-login">
            <svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Masuk
        </button>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        // Swap icon
        const eyeOpen = '<svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        const eyeOff  = '<svg viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
        btn.innerHTML = isPassword ? eyeOpen : eyeOff;
    }
</script>
@endsection
