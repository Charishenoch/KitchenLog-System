@extends('layouts.auth')

@section('title', 'Daftar Akun - KitchenLog')

@section('topbar')
    Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini &rarr;</a>
@endsection

@section('content')
    <h1>Daftar Akun</h1>
    <p class="auth-desc">Buat akun baru untuk mengakses sistem KitchenLog.</p>

    @if ($errors->any())
        <div class="alert-error" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:18px;font-size:.9rem;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form id="registerForm" method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Nama Lengkap --}}
        <div class="form-group">
            <label for="reg-name">Nama Lengkap</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input type="text" id="reg-name" name="name" placeholder="Masukkan nama lengkap" autocomplete="name" value="{{ old('name') }}">
            </div>
        </div>

        {{-- Email --}}
        <div class="form-group">
            <label for="reg-email">Email</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M2 7l10 7 10-7"/></svg>
                </span>
                <input type="email" id="reg-email" name="email" placeholder="contoh@email.com" autocomplete="email" value="{{ old('email') }}">
            </div>
        </div>

        {{-- Username --}}
        <div class="form-group">
            <label for="reg-username">Username</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input type="text" id="reg-username" name="username" placeholder="Buat username" autocomplete="username" value="{{ old('username') }}">
            </div>
        </div>

        {{-- Password --}}
        <div class="form-group">
            <label for="reg-password">Password</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 1 1 8 0v4"/></svg>
                </span>
                <input type="password" id="reg-password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password">
                <button type="button" class="toggle-password" onclick="togglePassword('reg-password', this)" aria-label="Toggle password visibility">
                    <svg class="eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>

        {{-- Konfirmasi Password --}}
        <div class="form-group">
            <label for="reg-password-confirm">Konfirmasi Password</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 1 1 8 0v4"/></svg>
                </span>
                <input type="password" id="reg-password-confirm" name="password_confirmation" placeholder="Ulangi password" autocomplete="new-password">
                <button type="button" class="toggle-password" onclick="togglePassword('reg-password-confirm', this)" aria-label="Toggle password visibility">
                    <svg class="eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
        </div>

        {{-- Pilih Role --}}
        <div class="form-group">
            <label for="reg-role">Pilih Role</label>
            <div class="input-wrapper">
                <span class="input-icon">
                    <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <select id="reg-role" name="role">
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih role</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                    <option value="dapur" {{ old('role') == 'dapur' ? 'selected' : '' }}>Staff Dapur</option>
                </select>
                <span class="select-arrow">
                    <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                </span>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary" id="btn-register" style="margin-top: 6px;">
            <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Daftar
        </button>
    </form>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        const eyeOpen = '<svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
        const eyeOff  = '<svg viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
        btn.innerHTML = isPassword ? eyeOpen : eyeOff;
    }
</script>
@endsection
