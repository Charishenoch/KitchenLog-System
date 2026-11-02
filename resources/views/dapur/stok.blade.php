@extends('layouts.dapur')
@section('title','Stok Bahan Baku')
@section('content')
<div class="page-hd">
    <div><h1>Stok Bahan Baku</h1><p>Lihat dan pantau jumlah stok semua bahan baku dapur.</p></div>
</div>

{{-- Filter bar --}}
<div class="card" style="margin-bottom:16px">
    <div class="card-body" style="padding:14px 16px">
        <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
            <input name="search" value="{{ $search }}" class="form-control" style="max-width:260px" placeholder="🔍 Cari nama bahan...">
            <select name="filter" class="form-control" style="max-width:180px" onchange="this.form.submit()">
                <option value="semua"   {{ $filter==='semua'  ?'selected':'' }}>Semua Status</option>
                <option value="aman"    {{ $filter==='aman'   ?'selected':'' }}>Stok Aman</option>
                <option value="menipis" {{ $filter==='menipis'?'selected':'' }}>Stok Menipis</option>
            </select>
            <button class="btn btn-primary btn-sm">Cari</button>
            @if($search || $filter!=='semua')
                <a href="{{ route('dapur.stok') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Daftar Bahan Baku ({{ $bahans->total() }} item)</span>
        <span style="font-size:12px;color:#6b7280">FIFO — bahan masuk lebih awal digunakan lebih dulu</span>
    </div>
    @if($bahans->isEmpty())
        <div class="empty-state"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg><h3>Tidak ada bahan ditemukan</h3></div>
    @else
    <table class="data-table">
        <thead>
            <tr><th>No</th><th>Nama Bahan</th><th>Stok Tersisa</th><th>Batas Min.</th><th>Satuan</th><th>Status</th><th>Tgl Masuk</th></tr>
        </thead>
        <tbody>
            @foreach($bahans as $i => $b)
            <tr>
                <td>{{ ($bahans->currentPage()-1)*$bahans->perPage()+$i+1 }}</td>
                <td style="font-weight:600;color:#111827">
                    <span class="dot {{ $b->stok<=0?'dot-red':($b->stok<=$b->batas_minimum?'dot-orange':'dot-green') }}"></span>
                    {{ $b->nama_bahan }}
                </td>
                <td style="font-weight:700;font-size:15px;color:{{ $b->stok<=$b->batas_minimum?'#dc2626':'#15803d' }}">{{ $b->stok }}</td>
                <td style="color:#6b7280">{{ $b->batas_minimum }}</td>
                <td>{{ $b->satuan }}</td>
                <td>
                    @if($b->stok <= 0)
                        <span class="badge badge-red">Habis</span>
                    @elseif($b->stok <= $b->batas_minimum)
                        <span class="badge badge-orange">Stok Menipis</span>
                    @else
                        <span class="badge badge-green">Stok Aman</span>
                    @endif
                </td>
                <td style="color:#6b7280;font-size:12px">
                    @if($b->tgl_masuk) {{ \Carbon\Carbon::parse($b->tgl_masuk)->format('d M Y') }} @else — @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-wrap">
        <span>{{ $bahans->firstItem() }}–{{ $bahans->lastItem() }} dari {{ $bahans->total() }}</span>
        {{ $bahans->links('pagination::simple-bootstrap-4') }}
    </div>
    @endif
</div>
@endsection
