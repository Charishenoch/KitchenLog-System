@extends('layouts.app')
@section('title','Notifikasi Stok')
@section('content')
<div class="page-hd">
    <div><h1>Notifikasi Stok Minimum</h1><p>Peringatan stok bahan yang hampir habis.</p></div>
    @if($unread > 0)
    <form method="POST" action="{{ route('notifikasi.bacaSemua') }}">
        @csrf
        <button type="submit" class="btn btn-secondary">
            <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Tandai Semua Dibaca
        </button>
    </form>
    @endif
</div>

@if($unread > 0)
<div class="alert alert-warning">
    <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <strong>{{ $unread }} notifikasi belum dibaca.</strong> Segera periksa dan lakukan pengadaan bahan.
</div>
@endif

<div class="card">
    <div class="card-head">
        <span class="card-title">Semua Notifikasi ({{ $notifikasi->total() }})</span>
        <form method="GET" style="display:flex;gap:8px;">
            <select name="status" class="form-control" style="width:170px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="belum_dibaca" {{ request('status')==='belum_dibaca'?'selected':'' }}>Belum Dibaca</option>
                <option value="dibaca"       {{ request('status')==='dibaca'?'selected':'' }}>Sudah Dibaca</option>
            </select>
        </form>
    </div>

    @if($notifikasi->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <h3>Tidak ada notifikasi</h3>
            <p>Semua stok bahan baku dalam kondisi aman.</p>
        </div>
    @else
        @foreach($notifikasi as $n)
        <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 18px;border-bottom:1px solid #f3f4f6;background:{{ $n->status==='belum_dibaca'?'#fffbeb':'#fff' }};">
            <div style="width:40px;height:40px;border-radius:10px;background:{{ $n->status==='belum_dibaca'?'#fff7ed':'#f3f4f6' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:{{ $n->status==='belum_dibaca'?'#f97316':'#9ca3af' }};fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div style="flex:1;">
                <div style="font-size:13px;font-weight:{{ $n->status==='belum_dibaca'?'600':'400' }};color:#111827;">{{ $n->pesan }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:3px;">{{ \Carbon\Carbon::parse($n->tgl_kirim)->locale('id')->diffForHumans() }} · Bahan: {{ $n->bahanBaku->nama_bahan ?? 'Dihapus' }}</div>
            </div>
            <div style="display:flex;gap:6px;flex-shrink:0;">
                @if($n->status === 'belum_dibaca')
                <form method="POST" action="{{ route('notifikasi.baca', $n->id) }}">@csrf
                    <button class="btn btn-secondary btn-sm">Tandai Dibaca</button>
                </form>
                @else
                    <span class="badge badge-green">Dibaca</span>
                @endif
                <form method="POST" action="{{ route('notifikasi.destroy', $n->id) }}" onsubmit="return confirm('Hapus notifikasi ini?')">@csrf @method('DELETE')
                    <button class="btn btn-danger btn-sm">Hapus</button>
                </form>
            </div>
        </div>
        @endforeach
        <div class="pagination-wrap">
            <span>{{ $notifikasi->firstItem() }}–{{ $notifikasi->lastItem() }} dari {{ $notifikasi->total() }}</span>
            {{ $notifikasi->links('pagination::simple-bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
