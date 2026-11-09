@extends('layouts.kasir')
@section('title','Stok Bahan')
@push('styles')
<style>
.batch-mini { font-size:11px; color:#6b7280; margin-top:3px; }
.batch-mini span { display:inline-block; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:4px; padding:1px 6px; margin:1px 2px 1px 0; white-space:nowrap; }
.batch-mini .fifo-first { background:#dbeafe; border-color:#93c5fd; color:#1d4ed8; font-weight:600; }
</style>
@endpush
@section('content')
<div class="page-hd">
    <div><h1>Stok Bahan</h1><p>Pantau ketersediaan bahan baku beserta batch masuk (FIFO) sebelum bertransaksi.</p></div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Total: {{ $bahans->total() }} bahan</span>
        <form method="GET" style="display:flex;gap:8px;">
            <input name="search" value="{{ request('search') }}" placeholder="Cari bahan..." class="form-control" style="width:220px;">
            <button type="submit" class="btn btn-primary">Cari</button>
            @if(request('search'))<a href="{{ route('kasir.stok') }}" class="btn btn-secondary">Reset</a>@endif
        </form>
    </div>

    @if($bahans->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            <h3>Tidak ada data</h3><p>Belum ada bahan baku yang terdaftar.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr><th>No</th><th>Nama Bahan</th><th>Stok Tersisa</th><th>Satuan</th><th>Min. Stok</th><th>Batch per Tgl Masuk (FIFO)</th><th>Kadaluwarsa</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($bahans as $i => $b)
                @php
                    $menipis  = $b->stok <= $b->batas_minimum;
                    $exp      = $b->tgl_kadaluarsa && $b->tgl_kadaluarsa->isPast();
                    $pct      = $b->batas_minimum > 0 ? min(100, round($b->stok / $b->batas_minimum * 100)) : 100;
                    $barColor = $exp ? '#ef4444' : ($menipis ? '#f97316' : '#16a34a');
                    $batches  = $b->penerimaanBahan;
                @endphp
                <tr>
                    <td style="vertical-align:top">{{ ($bahans->currentPage()-1)*$bahans->perPage()+$i+1 }}</td>
                    <td style="font-weight:500;vertical-align:top">{{ $b->nama_bahan }}</td>
                    <td style="vertical-align:top">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-weight:700;color:{{ $menipis?'#dc2626':'#15803d' }};min-width:40px;">{{ $b->stok }}</span>
                            <div style="flex:1;background:#f3f4f6;border-radius:4px;height:6px;min-width:60px;">
                                <div style="width:{{ $pct }}%;background:{{ $barColor }};height:6px;border-radius:4px;transition:width .3s;"></div>
                            </div>
                        </div>
                    </td>
                    <td style="vertical-align:top">{{ $b->satuan }}</td>
                    <td style="vertical-align:top">{{ $b->batas_minimum }}</td>

                    {{-- Kolom batch FIFO per tgl masuk --}}
                    <td>
                        @if($batches->isEmpty())
                            <span style="color:#9ca3af;font-size:11px">—</span>
                        @else
                        <div class="batch-mini">
                            @foreach($batches as $idx => $batch)
                            <span class="{{ $idx===0?'fifo-first':'' }}" title="{{ $idx===0?'Digunakan lebih dulu (FIFO)':'' }}">
                                {{ \Carbon\Carbon::parse($batch->tgl_masuk)->format('d/m/Y') }}
                                : {{ $batch->sisa }} {{ $b->satuan }}
                                @if($idx===0) ★@endif
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </td>

                    <td style="color:{{ $exp?'#dc2626':'#374151' }};vertical-align:top">
                        {{ $b->tgl_kadaluarsa ? $b->tgl_kadaluarsa->format('d/m/Y') : '-' }}
                    </td>
                    <td style="vertical-align:top">
                        @if($exp)         <span class="badge badge-red">Kedaluwarsa</span>
                        @elseif($menipis) <span class="badge badge-orange">Menipis</span>
                        @else             <span class="badge badge-green">Aman</span>
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
