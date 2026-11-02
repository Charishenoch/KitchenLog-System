@extends('layouts.app')
@section('title','Laporan Inventaris')
@section('content')
<div class="page-hd">
    <div><h1>Laporan Inventaris</h1><p>Laporan stok, transaksi, dan limbah berdasarkan periode.</p></div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:16px;">
    <div class="card-body">
        <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari->format('Y-m-d') }}" class="form-control">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai->format('Y-m-d') }}" class="form-control">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">Jenis Laporan</label>
                <select name="jenis" class="form-control">
                    <option value="stok"       {{ $jenis==='stok'?'selected':'' }}>Stok Inventaris</option>
                    <option value="transaksi"  {{ $jenis==='transaksi'?'selected':'' }}>Transaksi</option>
                    <option value="limbah"     {{ $jenis==='limbah'?'selected':'' }}>Limbah</option>
                    <option value="penerimaan" {{ $jenis==='penerimaan'?'selected':'' }}>Penerimaan Bahan</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
        </form>
    </div>
</div>

@if($jenis === 'stok')
{{-- Laporan Stok --}}
<div class="card">
    <div class="card-head">
        <span class="card-title">Laporan Stok Inventaris Saat Ini</span>
        <span style="font-size:12px;color:#6b7280;">{{ $laporanStok->count() }} bahan</span>
    </div>
    @if($laporanStok->isEmpty())
        <div class="empty-state"><p>Belum ada data bahan baku.</p></div>
    @else
    <table class="data-table">
        <thead><tr><th>No</th><th>Nama Bahan</th><th>Stok Tersisa</th><th>Satuan</th><th>Min. Stok</th><th>Harga/Satuan</th><th>Nilai Stok</th><th>Status</th></tr></thead>
        <tbody>
            @php $totalNilai = 0; @endphp
            @foreach($laporanStok as $i => $b)
            @php $nilai = $b->stok * $b->harga; $totalNilai += $nilai; $menipis = $b->stok <= $b->batas_minimum; @endphp
            <tr>
                <td>{{ $i+1 }}</td>
                <td style="font-weight:500;">{{ $b->nama_bahan }}</td>
                <td style="color:{{ $menipis?'#dc2626':'#15803d' }};font-weight:600;">{{ $b->stok }}</td>
                <td>{{ $b->satuan }}</td>
                <td>{{ $b->batas_minimum }}</td>
                <td>{{ $b->harga ? 'Rp '.number_format($b->harga,0,',','.') : '-' }}</td>
                <td>{{ $b->harga ? 'Rp '.number_format($nilai,0,',','.') : '-' }}</td>
                <td>@if($menipis)<span class="badge badge-orange">Menipis</span>@else<span class="badge badge-green">Aman</span>@endif</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f9fafb;font-weight:600;">
                <td colspan="6" style="padding:10px 14px;text-align:right;color:#374151;">Total Nilai Stok:</td>
                <td style="padding:10px 14px;color:#111827;">Rp {{ number_format($totalNilai,0,',','.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>

@elseif($jenis === 'transaksi')
{{-- Summary + Chart --}}
<div class="stats-row stats-row-3" style="margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-icon-box si-purple"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
        <div class="stat-info"><label>Total Transaksi</label><div class="stat-value">{{ $laporanTransaksi->count() }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-green"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="stat-info"><label>Total Pendapatan</label><div class="stat-value" style="font-size:18px;">Rp {{ number_format($totalPendapatan,0,',','.') }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-blue"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
        <div class="stat-info"><label>Rata-rata per Transaksi</label><div class="stat-value" style="font-size:18px;">Rp {{ $laporanTransaksi->count() > 0 ? number_format($totalPendapatan/$laporanTransaksi->count(),0,',','.') : '0' }}</div></div>
    </div>
</div>
@if(count($chartLabels) > 1)
<div class="card" style="margin-bottom:16px;">
    <div class="card-head"><span class="card-title">Grafik Pendapatan Harian</span></div>
    <div class="card-body"><canvas id="laporanChart" height="100"></canvas></div>
</div>
@endif
<div class="card">
    <div class="card-head"><span class="card-title">Detail Transaksi ({{ $laporanTransaksi->count() }})</span></div>
    @if($laporanTransaksi->isEmpty())<div class="empty-state"><p>Tidak ada transaksi dalam periode ini.</p></div>
    @else
    <table class="data-table">
        <thead><tr><th>No</th><th>Tanggal</th><th>Menu</th><th>Porsi</th><th>Total</th><th>Kasir</th></tr></thead>
        <tbody>
            @foreach($laporanTransaksi as $i => $t)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y H:i') }}</td>
                <td>{{ $t->menu->nama_menu ?? '-' }}</td>
                <td>{{ $t->jumlah_porsi }}</td>
                <td>Rp {{ number_format($t->total_harga,0,',','.') }}</td>
                <td>{{ $t->kasir->nama ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@elseif($jenis === 'limbah')
<div class="card">
    <div class="card-head"><span class="card-title">Laporan Limbah ({{ $laporanLimbah->count() }} catatan)</span></div>
    @if($laporanLimbah->isEmpty())<div class="empty-state"><p>Tidak ada data limbah dalam periode ini.</p></div>
    @else
    <table class="data-table">
        <thead><tr><th>No</th><th>Tanggal</th><th>Bahan Baku</th><th>Jumlah</th><th>Alasan</th><th>Dicatat Oleh</th></tr></thead>
        <tbody>
            @foreach($laporanLimbah as $i => $l)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y H:i') }}</td>
                <td>{{ $l->bahanBaku->nama_bahan ?? '-' }}</td>
                <td>{{ $l->jumlah }} {{ $l->bahanBaku->satuan ?? '' }}</td>
                <td>{{ $l->alasan }}</td>
                <td>{{ $l->staf->nama ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@else
{{-- Penerimaan --}}
<div class="card">
    <div class="card-head"><span class="card-title">Laporan Penerimaan Bahan ({{ $laporanPenerimaan->count() }})</span></div>
    @if($laporanPenerimaan->isEmpty())<div class="empty-state"><p>Tidak ada penerimaan dalam periode ini.</p></div>
    @else
    <table class="data-table">
        <thead><tr><th>No</th><th>Tgl Masuk</th><th>Bahan Baku</th><th>Jumlah Diterima</th><th>Sisa Stok</th><th>Kedaluwarsa</th><th>Staf</th></tr></thead>
        <tbody>
            @foreach($laporanPenerimaan as $i => $p)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $p->tgl_masuk->format('d/m/Y') }}</td>
                <td>{{ $p->bahanBaku->nama_bahan ?? '-' }}</td>
                <td>{{ $p->jumlah }} {{ $p->bahanBaku->satuan ?? '' }}</td>
                <td>{{ $p->sisa }}</td>
                <td>{{ $p->tgl_kadaluarsa ? $p->tgl_kadaluarsa->format('d/m/Y') : '-' }}</td>
                <td>{{ $p->staf->nama ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif
@endsection

@push('scripts')
@if($jenis === 'transaksi' && count($chartLabels) > 1)
<script>
new Chart(document.getElementById('laporanChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: @json($chartData),
            borderColor: '#16a34a',
            backgroundColor: 'rgba(22,163,74,.08)',
            borderWidth: 2.5,
            pointRadius: 4,
            pointBackgroundColor: '#16a34a',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#9ca3af' } },
            y: { grid: { color: '#f3f4f6' }, ticks: { font: { size: 11 }, color: '#9ca3af', callback: v => 'Rp '+v.toLocaleString('id-ID') } }
        }
    }
});
</script>
@endif
@endpush
