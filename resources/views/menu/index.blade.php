@extends('layouts.app')
@section('title','Kelola Menu')
@section('content')
<div class="page-hd">
    <div><h1>Kelola Menu</h1><p>Daftar semua menu yang tersedia di restoran.</p></div>
    <a href="{{ route('menu.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Menu
    </a>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Total: {{ $menu->total() }} menu</span>
        <form method="GET" style="display:flex;gap:8px;">
            <input name="search" value="{{ request('search') }}" placeholder="Cari menu..." class="form-control" style="width:200px;">
            <select name="kategori" class="form-control" style="width:150px;" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                @foreach($kategoris as $k)
                    <option value="{{ $k }}" {{ request('kategori')===$k?'selected':'' }}>{{ $k }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(request('search') || request('kategori'))
                <a href="{{ route('menu.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    @if($menu->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3v7"/></svg>
            <h3>Belum ada menu</h3>
            <p>Mulai tambahkan menu untuk restoran Anda.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($menu as $i => $m)
                <tr>
                    <td>{{ ($menu->currentPage()-1)*$menu->perPage() + $i + 1 }}</td>
                    <td style="font-weight:500;">{{ $m->nama_menu }}</td>
                    <td><span class="badge badge-blue">{{ $m->kategori }}</span></td>
                    <td>Rp {{ number_format($m->harga,0,',','.') }}</td>
                    <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#6b7280;">{{ $m->deskripsi ?? '-' }}</td>
                    <td>
                        @if($m->tersedia)
                            <span class="badge badge-green">Tersedia</span>
                        @else
                            <span class="badge badge-gray">Tidak Tersedia</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ route('resep.edit', $m->id) }}" class="btn btn-warning btn-sm">Resep</a>
                            <a href="{{ route('menu.edit', $m) }}" class="btn btn-secondary btn-sm">
                                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </a>
                            <form method="POST" action="{{ route('menu.destroy', $m) }}" onsubmit="return confirm('Hapus menu ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-wrap">
            <span>Menampilkan {{ $menu->firstItem() }}–{{ $menu->lastItem() }} dari {{ $menu->total() }} data</span>
            <div class="pagination-btns">{{ $menu->links('pagination::simple-bootstrap-4') }}</div>
        </div>
    @endif
</div>
@endsection
