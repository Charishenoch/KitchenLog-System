@extends('layouts.app')
@section('title','Setoran Kasir')
@section('content')
<div class="page-hd">
    <div><h1>Setoran Kasir</h1><p>Kelola pendapatan yang disetor oleh kasir.</p></div>
</div>

@if(session('success'))
<div class="alert alert-success"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>{{ session('success') }}</div>
@endif

<div class="stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:18px;">
    <div class="stat-card">
        <div class="stat-icon-box si-green"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="stat-info"><label>Total Setoran</label><div class="stat-value" style="font-size:16px;">Rp {{ number_format($totalSetoran,0,',','.') }}</div><div class="stat-unit">semua periode</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-blue"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="stat-info"><label>Sudah Diterima</label><div class="stat-value" style="font-size:16px;">Rp {{ number_format($totalDiterima,0,',','.') }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-orange"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="stat-info"><label>Menunggu Konfirmasi</label><div class="stat-value" style="font-size:16px;">Rp {{ number_format($totalPending,0,',','.') }}</div></div>
    </div>
</div>

<div class="card">
    <div class="card-head"><span class="card-title">Daftar Setoran ({{ $setoran->total() }})</span></div>

    @if($setoran->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <h3>Belum ada setoran</h3><p>Kasir belum melakukan setoran pendapatan.</p>
        </div>
    @else
        <table class="data-table">
            <thead><tr><th>No</th><th>Kasir</th><th>Tanggal Setor</th><th>Jumlah</th><th>Keterangan</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($setoran as $i => $s)
                <tr>
                    <td>{{ ($setoran->currentPage()-1)*$setoran->perPage()+$i+1 }}</td>
                    <td style="font-weight:500;">{{ $s->kasir->nama ?? $s->kasir->name ?? '-' }}</td>
                    <td>{{ $s->tanggal_setor->format('d/m/Y') }}</td>
                    <td style="font-weight:700;color:#15803d;">Rp {{ number_format($s->jumlah,0,',','.') }}</td>
                    <td>{{ $s->keterangan ?: '-' }}</td>
                    <td>
                        @if($s->status === 'diterima')
                            <span class="badge badge-green">Diterima</span>
                        @else
                            <span class="badge badge-orange">Pending</span>
                        @endif
                    </td>
                    <td>
                        @if($s->status === 'pending')
                        <form method="POST" action="{{ route('setoran.terima', $s->id) }}">
                            @csrf
                            <button class="btn btn-primary btn-sm">✓ Terima</button>
                        </form>
                        @else
                        <span style="font-size:12px;color:#9ca3af;">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-wrap">
            <span>{{ $setoran->firstItem() }}–{{ $setoran->lastItem() }} dari {{ $setoran->total() }}</span>
            {{ $setoran->links('pagination::simple-bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
