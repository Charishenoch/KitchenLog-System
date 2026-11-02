@extends('layouts.app')
@section('title','Kelola Bahan Baku')
@section('content')
<div class="page-hd">
    <div><h1>Kelola Bahan Baku</h1><p>Kelola data bahan baku, stok, dan informasi kedaluwarsa.</p></div>
    <a href="{{ route('bahan-baku.create') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Bahan Baku
    </a>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Total: {{ $bahans->total() }} bahan</span>
        <form method="GET" style="display:flex;gap:8px;">
            <input name="search" value="{{ request('search') }}" placeholder="Cari bahan..." class="form-control" style="width:200px;">
            <select name="status" class="form-control" style="width:165px;" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="menipis"    {{ request('status')==='menipis'    ? 'selected' : '' }}>Stok Menipis</option>
                <option value="aman"       {{ request('status')==='aman'       ? 'selected' : '' }}>Stok Aman</option>
                <option value="kadaluarsa" {{ request('status')==='kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
            </select>
            <button type="submit" class="btn btn-secondary">Cari</button>
            @if(request('search')||request('status'))<a href="{{ route('bahan-baku.index') }}" class="btn btn-secondary">Reset</a>@endif
        </form>
    </div>

    @if($bahans->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            <h3>Belum ada bahan baku</h3>
            <p>Tambahkan bahan baku pertama Anda.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th><th>Nama Bahan</th><th>Stok</th><th>Satuan</th><th>Min. Stok</th>
                    <th>Harga/Satuan</th><th>Tgl Masuk</th><th>Kedaluwarsa</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bahans as $i => $b)
                @php
                    $menipis = $b->stok <= $b->batas_minimum;
                    $kadal   = $b->kadaluarsa_terdekat;
                    $exp     = $kadal && $kadal->isPast();
                    $nearExp = $kadal && !$exp && $kadal->diffInDays(today()) <= 7;
                @endphp
                <tr>
                    <td>{{ ($bahans->currentPage()-1)*$bahans->perPage()+$i+1 }}</td>
                    <td style="font-weight:500;">{{ $b->nama_bahan }}</td>
                    <td style="color:{{ $menipis ? '#dc2626' : '#15803d' }};font-weight:600;">{{ $b->stok }}</td>
                    <td>{{ $b->satuan }}</td>
                    <td>{{ $b->batas_minimum }}</td>
                    <td>{{ $b->harga ? 'Rp '.number_format($b->harga,0,',','.') : '-' }}</td>
                    <td>{{ $b->tgl_masuk ? $b->tgl_masuk->format('d/m/Y') : '-' }}</td>
                    <td style="color:{{ $exp ? '#dc2626' : ($nearExp ? '#d97706' : 'inherit') }};" title="{{ $kadal ? 'Batch terdekat' : '' }}">
                        {{ $kadal ? $kadal->format('d/m/Y') : '-' }}
                        @if($nearExp)
                            <span title="Batch ini akan kadaluarsa dalam {{ $kadal->diffInDays(today()) }} hari!" style="font-size:11px;color:#d97706;display:block;">⚠ {{ $kadal->diffInDays(today()) }} hari lagi</span>
                        @elseif($exp)
                            <span style="font-size:11px;color:#dc2626;display:block;">✗ Sudah lewat</span>
                        @endif
                    </td>
                    <td>
                        @if($exp) <span class="badge badge-red">Kedaluwarsa</span>
                        @elseif($nearExp) <span class="badge badge-orange">Segera Exp</span>
                        @elseif($menipis) <span class="badge badge-orange">Menipis</span>
                        @else <span class="badge badge-green">Aman</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions-cell">
                            <a href="{{ route('bahan-baku.edit', $b) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('bahan-baku.destroy', $b) }}" onsubmit="return confirm('Hapus bahan ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-wrap">
            <span>Menampilkan {{ $bahans->firstItem() }}–{{ $bahans->lastItem() }} dari {{ $bahans->total() }}</span>
            {{ $bahans->links('pagination::simple-bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
