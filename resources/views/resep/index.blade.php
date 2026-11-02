@extends('layouts.app')
@section('title','Kelola Resep')
@section('content')
<div class="page-hd">
    <div><h1>Kelola Resep</h1><p>Atur komposisi bahan baku untuk setiap menu.</p></div>
    <a href="{{ route('resep.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Resep
    </a>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Resep per Menu ({{ $menus->total() }} menu)</span>
        <form method="GET" style="display:flex;gap:8px;">
            <input name="search" value="{{ request('search') }}" placeholder="Cari menu..." class="form-control" style="width:220px;">
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(request('search'))<a href="{{ route('resep.index') }}" class="btn btn-secondary">Reset</a>@endif
        </form>
    </div>

    @if($menus->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <h3>Belum ada data</h3>
            <p>Tambahkan menu terlebih dahulu, lalu buat resepnya.</p>
        </div>
    @else
        @foreach($menus as $m)
        <div style="padding:14px 18px;border-bottom:1px solid #f3f4f6;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <div>
                    <span style="font-weight:600;font-size:14px;color:#111827;">{{ $m->nama_menu }}</span>
                    <span class="badge badge-blue" style="margin-left:8px;">{{ $m->kategori }}</span>
                </div>
                <div style="display:flex;gap:6px;">
                    <a href="{{ route('resep.edit', $m->id) }}" class="btn btn-warning btn-sm">
                        <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Resep
                    </a>
                    <form method="POST" action="{{ route('resep.destroy', $m->id) }}" onsubmit="return confirm('Hapus semua resep menu ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Hapus Resep</button>
                    </form>
                </div>
            </div>
            @if($m->resep->isEmpty())
                <div style="font-size:12px;color:#9ca3af;font-style:italic;padding:4px 0;">Belum ada komposisi bahan baku — <a href="{{ route('resep.edit', $m->id) }}" style="color:#16a34a;">Tambah sekarang</a></div>
            @else
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($m->resep as $r)
                        <span style="background:#f3f4f6;color:#374151;font-size:12px;padding:4px 10px;border-radius:20px;">
                            {{ $r->bahanBaku->nama_bahan ?? '?' }} — {{ $r->jumlah }} {{ $r->satuan }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>
        @endforeach
        <div class="pagination-wrap">
            <span>Menampilkan {{ $menus->firstItem() }}–{{ $menus->lastItem() }} dari {{ $menus->total() }}</span>
            {{ $menus->links('pagination::simple-bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
