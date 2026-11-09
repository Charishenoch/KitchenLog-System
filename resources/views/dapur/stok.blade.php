@extends('layouts.dapur')
@section('title','Stok Bahan Baku')
@push('styles')
<style>
.batch-table { width:100%; border-collapse:collapse; font-size:11px; margin-top:4px; }
.batch-table th { background:#f9fafb; color:#6b7280; padding:3px 7px; font-weight:600; text-align:left; border-bottom:1px solid #e5e7eb; }
.batch-table td { padding:3px 7px; border-bottom:1px solid #f3f4f6; color:#374151; }
.batch-table tr:last-child td { border-bottom:none; }
.batch-wrap { background:#f9fafb; border:1px solid #e5e7eb; border-radius:6px; overflow:hidden; margin-top:4px; }
.fifo-label { display:inline-block; background:#dbeafe; color:#1d4ed8; font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; margin-left:4px; vertical-align:middle; }
</style>
@endpush
@section('content')
<div class="page-hd">
    <div><h1>Stok Bahan Baku</h1><p>Lihat dan pantau jumlah stok semua bahan baku dapur, terpisah per tanggal masuk (FIFO).</p></div>
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
        <span style="font-size:12px;background:#dbeafe;padding:3px 10px;border-radius:6px;color:#1d4ed8;font-weight:500">FIFO — batch per tanggal masuk dipisah</span>
    </div>
    @if($bahans->isEmpty())
        <div class="empty-state"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg><h3>Tidak ada bahan ditemukan</h3></div>
    @else
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Bahan</th>
                <th>Total Stok</th>
                <th>Batas Min.</th>
                <th>Satuan</th>
                <th>Status</th>
                <th>Batch per Tgl Masuk (FIFO)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bahans as $i => $b)
            <tr>
                <td style="vertical-align:top">{{ ($bahans->currentPage()-1)*$bahans->perPage()+$i+1 }}</td>
                <td style="font-weight:600;color:#111827;vertical-align:top">
                    <span class="dot {{ $b->stok<=0?'dot-red':($b->stok<=$b->batas_minimum?'dot-orange':'dot-green') }}"></span>
                    {{ $b->nama_bahan }}
                </td>
                <td style="font-weight:700;font-size:15px;color:{{ $b->stok<=$b->batas_minimum?'#dc2626':'#15803d' }};vertical-align:top">{{ $b->stok }}</td>
                <td style="color:#6b7280;vertical-align:top">{{ $b->batas_minimum }}</td>
                <td style="vertical-align:top">{{ $b->satuan }}</td>
                <td style="vertical-align:top">
                    @if($b->stok <= 0)
                        <span class="badge badge-red">Habis</span>
                    @elseif($b->stok <= $b->batas_minimum)
                        <span class="badge badge-orange">Stok Menipis</span>
                    @else
                        <span class="badge badge-green">Stok Aman</span>
                    @endif
                </td>
                {{-- Kolom batch FIFO per tgl masuk --}}
                <td>
                    @if($b->penerimaanBahan->isEmpty())
                        <span style="color:#9ca3af;font-size:11px">— belum ada batch —</span>
                    @else
                    <div class="batch-wrap">
                        <table class="batch-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tgl Masuk</th>
                                    <th>Sisa</th>
                                    <th>Kadaluarsa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($b->penerimaanBahan as $idx => $batch)
                                @php
                                    $exp = $batch->tgl_kadaluarsa && \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->isPast();
                                    $nearExp = $batch->tgl_kadaluarsa && !$exp && \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->diffInDays(today()) <= 7;
                                @endphp
                                <tr>
                                    <td>
                                        {{ $idx + 1 }}
                                        @if($idx === 0)
                                            <span class="fifo-label">NEXT</span>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap">{{ \Carbon\Carbon::parse($batch->tgl_masuk)->format('d M Y') }}</td>
                                    <td style="font-weight:700;color:#15803d">{{ $batch->sisa }} {{ $b->satuan }}</td>
                                    <td style="white-space:nowrap;color:{{ $exp?'#dc2626':($nearExp?'#ea580c':'#374151') }}">
                                        @if($batch->tgl_kadaluarsa)
                                            {{ \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->format('d M Y') }}
                                            @if($exp) <span style="color:#dc2626;font-weight:700">⚠Exp</span>
                                            @elseif($nearExp) <span style="color:#ea580c">⚠{{ \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->diffInDays(today()) }}hr</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
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
