@extends('layouts.app')
@section('title','Kelola Bahan Baku')
@push('styles')
<style>
.batch-tbl { width:100%; border-collapse:collapse; font-size:11px; margin-top:4px; }
.batch-tbl th { background:#f9fafb; color:#6b7280; padding:3px 7px; font-weight:600; text-align:left; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
.batch-tbl td { padding:4px 7px; border-bottom:1px solid #f3f4f6; color:#374151; vertical-align:middle; }
.batch-tbl tr:last-child td { border-bottom:none; }
.batch-wrap { background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; overflow:hidden; min-width:260px; }
.fifo-badge { display:inline-block; background:#dbeafe; color:#1d4ed8; font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; margin-left:3px; }
</style>
@endpush
@section('content')
<div class="page-hd">
    <div><h1>Kelola Bahan Baku</h1><p>Kelola data bahan baku, stok, dan batch masuk per tanggal (FIFO).</p></div>
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
                    <th>No</th><th>Nama Bahan</th><th>Total Stok</th><th>Satuan</th><th>Min. Stok</th>
                    <th>Harga/Satuan</th>
                    <th>Batch Aktif (FIFO) <span style="font-weight:400;font-size:10px;color:#9ca3af">— klik Edit untuk kelola batch</span></th>
                    <th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bahans as $i => $b)
                @php
                    $menipis = $b->stok <= $b->batas_minimum;
                    $kadal   = $b->kadaluarsa_terdekat;
                    $exp     = $kadal && $kadal->isPast();
                    $nearExp = $kadal && !$exp && $kadal->diffInDays(today()) <= 7;
                    $batches = $b->penerimaanBahan;
                @endphp
                <tr>
                    <td>{{ ($bahans->currentPage()-1)*$bahans->perPage()+$i+1 }}</td>
                    <td style="font-weight:600">{{ $b->nama_bahan }}</td>
                    <td style="color:{{ $menipis ? '#dc2626' : '#15803d' }};font-weight:700;font-size:15px">{{ $b->stok }}</td>
                    <td>{{ $b->satuan }}</td>
                    <td>{{ $b->batas_minimum }}</td>
                    <td>{{ $b->harga ? 'Rp '.number_format($b->harga,0,',','.') : '-' }}</td>

                    {{-- Info batch saja (read-only). Edit/Hapus batch lewat halaman Edit --}}
                    <td>
                        @if($batches->isEmpty())
                            <span style="color:#9ca3af;font-size:11px">— belum ada batch —</span>
                        @else
                        <div class="batch-wrap">
                            <table class="batch-tbl">
                                <thead>
                                    <tr><th>#</th><th>Tgl Masuk</th><th>Sisa</th><th>Kadaluarsa</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($batches as $idx => $batch)
                                    @php
                                        $bExp  = $batch->tgl_kadaluarsa && \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->isPast();
                                        $bNear = $batch->tgl_kadaluarsa && !$bExp && \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->diffInDays(today()) <= 7;
                                    @endphp
                                    <tr>
                                        <td>{{ $idx+1 }}@if($idx===0)<span class="fifo-badge">NEXT</span>@endif</td>
                                        <td style="white-space:nowrap;font-weight:600">{{ \Carbon\Carbon::parse($batch->tgl_masuk)->format('d/m/Y') }}</td>
                                        <td style="color:#15803d;font-weight:700">{{ $batch->sisa }} {{ $b->satuan }}</td>
                                        <td style="white-space:nowrap;color:{{ $bExp?'#dc2626':($bNear?'#ea580c':'#374151') }}">
                                            @if($batch->tgl_kadaluarsa)
                                                {{ \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->format('d/m/Y') }}
                                                @if($bExp)<span style="font-size:10px"> ⚠Exp</span>
                                                @elseif($bNear)<span style="font-size:10px"> ⚠Segera</span>
                                                @endif
                                            @else —
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                            <form method="POST" action="{{ route('bahan-baku.destroy', $b) }}" onsubmit="return confirm('Hapus bahan ini beserta semua batch?')">
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
