@extends('layouts.kasir')
@section('title','Riwayat Transaksi')
@section('content')
<div class="page-hd">
    <div><h1>Riwayat Transaksi</h1><p>Data transaksi shift Anda. Gunakan filter tanggal untuk lihat data historis.</p></div>
    <a href="{{ route('kasir.dashboard') }}" class="btn btn-primary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Transaksi Baru
    </a>
</div>

{{-- Banner status setoran --}}
@if($masihPending)
<div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:12px;padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
    <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#f97316;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <span style="font-size:13px;color:#c2410c;font-weight:500">
        ⏳ Setoran sedang menunggu konfirmasi admin. Transaksi setelah setoran <strong>tidak ditampilkan</strong> sampai admin konfirmasi.
    </span>
</div>
@elseif($sudahDiterima)
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
    <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#16a34a;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <span style="font-size:13px;color:#15803d;font-weight:500">
        ✓ Setoran sudah diterima admin. Menampilkan transaksi baru setelah konfirmasi.
    </span>
</div>
@endif

{{-- Stats --}}
<div class="stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:18px;">
    <div class="stat-card">
        <div class="stat-icon si-blue"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="stat-info"><label>Total Transaksi</label><div class="stat-value">{{ $totalTrx }}</div><div class="stat-unit">{{ request()->has('dari') ? 'periode terpilih' : 'shift ini' }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="stat-info">
            <label>Total Pendapatan</label>
            <div class="stat-value" style="font-size:16px;">Rp {{ number_format($totalPendapatan,0,',','.') }}</div>
            <div class="stat-unit">
                @if($masihPending) <span style="color:#f97316">⏳ menunggu konfirmasi</span>
                @elseif($sudahDiterima) <span style="color:#16a34a">✓ setelah konfirmasi</span>
                @else shift ini @endif
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div class="stat-info"><label>Rata-rata per Transaksi</label><div class="stat-value" style="font-size:16px;">Rp {{ $totalTrx > 0 ? number_format($totalPendapatan/$totalTrx,0,',','.') : '0' }}</div></div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">
            Daftar Transaksi
            @if(!request()->has('dari')) <span style="font-size:11px;background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:10px;font-weight:500;margin-left:6px">Shift ini</span>
            @else <span style="font-size:11px;background:#f3f4f6;color:#6b7280;padding:2px 8px;border-radius:10px;font-weight:500;margin-left:6px">Filter: {{ $dari }} s/d {{ $sampai }}</span>
            @endif
        </span>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <input type="date" name="dari"   value="{{ $dari }}"   class="form-control" style="width:140px;">
            <span style="font-size:13px;color:#6b7280;">s/d</span>
            <input type="date" name="sampai" value="{{ $sampai }}" class="form-control" style="width:140px;">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            @if(request()->has('dari'))
                <a href="{{ route('kasir.riwayat') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>

    @if(request()->has('dari'))
    <div style="padding:8px 18px;background:#fffbeb;border-bottom:1px solid #fef3c7;font-size:12px;color:#92400e">
        ⚠️ Mode filter historis — menampilkan <strong>semua</strong> transaksi di periode ini tanpa filter status setoran.
    </div>
    @endif

    @if($transaksi->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <h3>Belum ada transaksi</h3>
            <p>
                @if($masihPending) Tidak ada transaksi sebelum setoran dikirim.
                @else Belum ada transaksi di periode ini.
                @endif
            </p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr><th>No.</th><th>Tanggal & Waktu</th><th>Menu</th><th>Jumlah Porsi</th><th>Total Harga</th><th>Status</th></tr>
            </thead>
            <tbody>
                @foreach($transaksi as $i => $t)
                <tr>
                    <td>{{ ($transaksi->currentPage()-1)*$transaksi->perPage()+$i+1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y H:i') }}</td>
                    <td style="font-weight:500;">{{ $t->menu->nama_menu ?? '-' }}</td>
                    <td>{{ $t->jumlah_porsi }} porsi</td>
                    <td style="font-weight:600;color:#15803d;">Rp {{ number_format($t->total_harga,0,',','.') }}</td>
                    <td><span class="badge badge-green">Selesai</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-wrap">
            <span>Menampilkan {{ $transaksi->firstItem() }}–{{ $transaksi->lastItem() }} dari {{ $transaksi->total() }}</span>
            {{ $transaksi->links('pagination::simple-bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
