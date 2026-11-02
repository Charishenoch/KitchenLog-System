@extends('layouts.app')
@section('title','Kelola Pengguna')
@section('content')
<div class="page-hd">
    <div><h1>Kelola Pengguna</h1><p>Atur akun dan hak akses pengguna sistem.</p></div>
    <a href="{{ route('pengguna.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Pengguna
    </a>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Total: {{ $pengguna->total() }} pengguna</span>
        <form method="GET" style="display:flex;gap:8px;">
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama/email..." class="form-control" style="width:200px;">
            <select name="role" class="form-control" style="width:130px;" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="admin"  {{ request('role')==='admin'?'selected':'' }}>Admin</option>
                <option value="kasir"  {{ request('role')==='kasir'?'selected':'' }}>Kasir</option>
                <option value="dapur"  {{ request('role')==='dapur'?'selected':'' }}>Dapur</option>
            </select>
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(request('search')||request('role'))<a href="{{ route('pengguna.index') }}" class="btn btn-secondary">Reset</a>@endif
        </form>
    </div>

    @if($pengguna->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <h3>Belum ada pengguna</h3>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr><th>No</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Bergabung</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($pengguna as $i => $u)
                <tr>
                    <td>{{ ($pengguna->currentPage()-1)*$pengguna->perPage()+$i+1 }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:50%;background:{{ $u->id === auth()->id() ? '#16a34a' : '#6b7280' }};color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:600;flex-shrink:0;">
                                {{ strtoupper(substr($u->nama ?? 'U', 0, 1)) }}
                            </div>
                            <span style="font-weight:500;">{{ $u->nama }}
                                @if($u->id === auth()->id())<span style="font-size:11px;color:#16a34a;">(Anda)</span>@endif
                            </span>
                        </div>
                    </td>
                    <td style="color:#6b7280;">{{ $u->username ? '@'.$u->username : '-' }}</td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @if($u->role === 'admin') <span class="badge badge-purple">Admin</span>
                        @elseif($u->role === 'kasir') <span class="badge badge-blue">Kasir</span>
                        @else <span class="badge badge-orange">Dapur</span>
                        @endif
                    </td>
                    <td style="color:#6b7280;">{{ $u->created_at ? $u->created_at->format('d/m/Y') : '-' }}</td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ route('pengguna.edit', $u) }}" class="btn btn-secondary btn-sm">Edit</a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('pengguna.destroy', $u) }}" onsubmit="return confirm('Hapus pengguna {{ $u->nama }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-wrap">
            <span>Menampilkan {{ $pengguna->firstItem() }}–{{ $pengguna->lastItem() }} dari {{ $pengguna->total() }}</span>
            {{ $pengguna->links('pagination::simple-bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
