@extends('layouts.dapur')
@section('title','Notifikasi Stok')
@section('content')
<div class="page-hd">
    <div><h1>Notifikasi Stok Minimum</h1><p>Peringatan bahan baku yang mendekati atau melewati batas minimum.</p></div>
    @if($notifDapurCount > 0)
    <form method="POST" action="{{ route('dapur.notifikasi.baca-semua') }}">
        @csrf
        <button class="btn btn-secondary">
            <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Tandai Semua Dibaca
        </button>
    </form>
    @endif
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Semua Notifikasi ({{ $notifikasi->total() }})</span>
        @if($notifDapurCount > 0)
            <span class="badge badge-red">{{ $notifDapurCount }} belum dibaca</span>
        @else
            <span class="badge badge-green">Semua sudah dibaca</span>
        @endif
    </div>
    @if($notifikasi->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <h3>Tidak ada notifikasi</h3><p>Semua stok dalam kondisi aman.</p>
        </div>
    @else
    @foreach($notifikasi as $n)
    <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 18px;border-bottom:1px solid #f3f4f6;background:{{ $n->status==='belum_dibaca'?'#fffbeb':'#fff' }};transition:background .2s">
        <div style="width:40px;height:40px;border-radius:10px;background:{{ $n->status==='belum_dibaca'?'#fff7ed':'#f3f4f6' }};color:{{ $n->status==='belum_dibaca'?'#f97316':'#9ca3af' }};display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round">
                @if($n->status==='belum_dibaca')
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                @else
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                @endif
            </svg>
        </div>
        <div style="flex:1;min-width:0">
            <div style="font-size:13px;font-weight:{{ $n->status==='belum_dibaca'?'600':'400' }};color:#111827;margin-bottom:3px">
                {{ $n->pesan }}
            </div>
            <div style="font-size:11px;color:#9ca3af">
                {{ \Carbon\Carbon::parse($n->tgl_kirim)->locale('id')->diffForHumans() }}
                @if($n->bahanBaku) — <span style="font-weight:500;color:#6b7280">{{ $n->bahanBaku->nama_bahan }}</span> @endif
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
            @if($n->status==='belum_dibaca')
                <span class="badge badge-orange">Baru</span>
                <form method="POST" action="{{ route('dapur.notifikasi.baca', $n->id) }}" style="margin:0">
                    @csrf
                    <button class="btn btn-secondary btn-sm">Tandai Dibaca</button>
                </form>
            @else
                <span class="badge badge-gray">Dibaca</span>
            @endif
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
