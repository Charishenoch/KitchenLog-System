@extends('layouts.app')
@section('title','Edit Pengguna')
@section('content')
<div class="page-hd">
    <div><h1>Edit Pengguna</h1></div>
    <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">← Kembali</a>
</div>
<div class="card" style="max-width:560px;">
    <div class="card-head"><span class="card-title">Edit: {{ $pengguna->nama }}</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('pengguna.update', $pengguna) }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nama Lengkap <span style="color:#ef4444">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $pengguna->nama) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" value="{{ old('username', $pengguna->username) }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email <span style="color:#ef4444">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $pengguna->email) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
                <div class="form-group full">
                    <label>Role <span style="color:#ef4444">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="admin" {{ old('role',$pengguna->role)==='admin'?'selected':'' }}>Admin</option>
                        <option value="kasir" {{ old('role',$pengguna->role)==='kasir'?'selected':'' }}>Kasir</option>
                        <option value="dapur" {{ old('role',$pengguna->role)==='dapur'?'selected':'' }}>Dapur</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
