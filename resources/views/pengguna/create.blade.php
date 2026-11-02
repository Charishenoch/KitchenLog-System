@extends('layouts.app')
@section('title','Tambah Pengguna')
@section('content')
<div class="page-hd">
    <div><h1>Tambah Pengguna</h1></div>
    <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">← Kembali</a>
</div>
<div class="card" style="max-width:560px;">
    <div class="card-head"><span class="card-title">Form Pengguna Baru</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('pengguna.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nama Lengkap <span style="color:#ef4444">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-control" placeholder="Ahmad Danu Prasetyo" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="ahmaddanu">
                    <span class="form-hint">Opsional, hanya huruf/angka/underscore</span>
                </div>
                <div class="form-group">
                    <label>Email <span style="color:#ef4444">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="email@domain.com" required>
                </div>
                <div class="form-group">
                    <label>Password <span style="color:#ef4444">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password <span style="color:#ef4444">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="form-group full">
                    <label>Role / Hak Akses <span style="color:#ef4444">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" {{ old('role')==='admin'?'selected':'' }}>Admin — Akses penuh ke semua fitur</option>
                        <option value="kasir" {{ old('role')==='kasir'?'selected':'' }}>Kasir — Kelola transaksi</option>
                        <option value="dapur" {{ old('role')==='dapur'?'selected':'' }}>Dapur — Lihat stok & resep</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
                <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
