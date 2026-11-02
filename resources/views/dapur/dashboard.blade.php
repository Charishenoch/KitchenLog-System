@extends('layouts.dapur')
@section('title','Dashboard Staf Dapur')
@push('styles')
<style>
.dash-grid { display:grid; grid-template-columns:1fr 1fr 260px; gap:16px; }
.quick-actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; padding:14px; }
.qa-btn { display:flex; flex-direction:column; align-items:center; gap:8px; padding:18px 10px; border-radius:12px; font-size:12px; font-weight:600; text-align:center; text-decoration:none; transition:transform .15s, box-shadow .15s; }
.qa-btn:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.1); }
.qa-btn svg { width:28px; height:28px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
.qa-green { background:#dcfce7; color:#16a34a; }
.qa-red   { background:#fee2e2; color:#dc2626; }
.qa-blue  { background:#dbeafe; color:#2563eb; }
.qa-purple{ background:#f3e8ff; color:#7c3aed; }
.notif-row { display:flex; align-items:flex-start; gap:10px; padding:10px 14px; border-bottom:1px solid #f3f4f6; }
.notif-row:last-child { border-bottom:none; }
.notif-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.notif-icon svg { width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
.notif-ni-orange { background:#fff7ed; color:#f97316; }
.notif-ni-blue   { background:#dbeafe; color:#2563eb; }
.activity-row { display:flex; align-items:center; gap:10px; padding:10px 14px; border-bottom:1px solid #f3f4f6; }
.activity-row:last-child { border-bottom:none; }
.ai { width:34px; height:34px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.ai svg { width:16px; height:16px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
</style>
@endpush
@section('content')
<div class="page-hd">
    <div>
        <h1>Selamat Datang, {{ Auth::user()->nama ?? Auth::user()->name }}!</h1>
        <p>Pantau stok bahan, catat penerimaan dan limbah, serta pastikan operasional dapur berjalan lancar.</p>
    </div>
</div>

{{-- Stat Cards --}}
<div class="stats-row stats-4">
    <div class="stat-card">
        <div class="stat-icon si-green"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
        <div class="stat-info"><label>Total Bahan Baku</label><div class="stat-value">{{ $totalBahan }}</div><div class="stat-unit">jenis bahan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="stat-info"><label>Stok Menipis</label><div class="stat-value">{{ $stokMenipis }}</div><div class="stat-unit">jenis bahan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
        <div class="stat-info"><label>Penerimaan Hari Ini</label><div class="stat-value">{{ $penerimaanHariIni }}</div><div class="stat-unit">jenis bahan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-red"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></div>
        <div class="stat-info"><label>Limbah Hari Ini</label><div class="stat-value">{{ $limbahHariIni }}</div><div class="stat-unit">jenis bahan</div></div>
    </div>
</div>

{{-- Main grid --}}
<div class="dash-grid">
    {{-- Stok Bahan Baku table --}}
    <div class="card" style="grid-column:1;grid-row:1">
        <div class="card-head">
            <span class="card-title">Stok Bahan Baku</span>
            <a href="{{ route('dapur.stok') }}" class="card-link">Lihat Semua <svg viewBox="0 0 24 24" style="width:11px;height:11px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><polyline points="9 18 15 12 9 6"/></svg></a>
        </div>
        <table class="data-table">
            <thead><tr><th>No</th><th>Nama Bahan</th><th>Stok Tersisa</th><th>Satuan</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($stokBahans as $i => $b)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td style="font-weight:500">{{ $b->nama_bahan }}</td>
                    <td>{{ $b->stok }}</td>
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
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:24px">Belum ada data bahan baku</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Kolom tengah: Penerimaan & Limbah --}}
    <div style="grid-column:2;grid-row:1;display:flex;flex-direction:column;gap:16px">
        {{-- Penerimaan Terbaru --}}
        <div class="card">
            <div class="card-head">
                <span style="display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:#111827">
                    <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:#2563eb;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Penerimaan Bahan (Terbaru)
                </span>
                <a href="{{ route('dapur.penerimaan') }}" class="card-link">Lihat Semua</a>
            </div>
            @forelse($penerimaanTerbaru as $p)
            <div class="activity-row">
                <div class="ai" style="background:#dbeafe;color:#2563eb">
                    <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:12px;font-weight:600;color:#111827">{{ $p->bahanBaku->nama_bahan ?? '-' }}</div>
                    <div style="font-size:11px;color:#6b7280">{{ $p->tgl_masuk ? \Carbon\Carbon::parse($p->tgl_masuk)->format('d M Y') : '-' }}</div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:12px;font-weight:700;color:#111827">{{ $p->jumlah }} {{ $p->bahanBaku->satuan ?? '' }}</div>
                    <span class="badge badge-blue" style="font-size:10px">Diterima</span>
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:#9ca3af;font-size:13px">Belum ada penerimaan</div>
            @endforelse
        </div>

        {{-- Notifikasi --}}
        <div class="card">
            <div class="card-head">
                <span style="display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:#111827">
                    <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:#f97316;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    Notifikasi
                </span>
                <a href="{{ route('dapur.notifikasi') }}" class="card-link">Lihat Semua</a>
            </div>
            @forelse($notifStok as $n)
            <div class="notif-row">
                <div class="notif-icon {{ $n->status==='belum_dibaca'?'notif-ni-orange':'notif-ni-blue' }}">
                    <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:12px;font-weight:500;color:#111827">{{ $n->pesan }}</div>
                    <div style="font-size:11px;color:#9ca3af">{{ \Carbon\Carbon::parse($n->tgl_kirim)->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:#9ca3af;font-size:13px">Semua stok aman</div>
            @endforelse
        </div>
    </div>

    {{-- Kolom kanan: Limbah & Aksi Cepat --}}
    <div style="grid-column:3;grid-row:1;display:flex;flex-direction:column;gap:16px">
        {{-- Limbah Terbaru --}}
        <div class="card">
            <div class="card-head">
                <span style="display:flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:#111827">
                    <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:#dc2626;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    Limbah / Kerusakan
                </span>
                <a href="{{ route('dapur.limbah') }}" class="card-link">Lihat Semua</a>
            </div>
            @forelse($limbahTerbaru as $l)
            <div class="activity-row">
                <div class="ai" style="background:#fee2e2;color:#dc2626">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:12px;font-weight:600;color:#111827">{{ $l->bahanBaku->nama_bahan ?? '-' }}</div>
                    <div style="font-size:11px;color:#6b7280">{{ \Carbon\Carbon::parse($l->tanggal)->format('d M Y') }}</div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:12px;font-weight:700;color:#dc2626">{{ $l->jumlah }} {{ $l->bahanBaku->satuan ?? '' }}</div>
                    <span class="badge badge-red" style="font-size:10px">{{ \Illuminate\Support\Str::limit($l->alasan,8) }}</span>
                </div>
            </div>
            @empty
            <div style="padding:24px;text-align:center;color:#9ca3af;font-size:13px">Belum ada limbah</div>
            @endforelse
        </div>

        {{-- Aksi Cepat --}}
        <div class="card">
            <div class="card-head"><span class="card-title">⚡ Aksi Cepat</span></div>
            <div class="quick-actions">
                <a href="{{ route('dapur.penerimaan') }}" class="qa-btn qa-blue">
                    <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    Catat Penerimaan Bahan
                </a>
                <a href="{{ route('dapur.alokasi.index') }}" class="qa-btn qa-green">
                    <svg viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
                    Alokasi Menu Hari Ini
                </a>
                <a href="{{ route('dapur.limbah') }}" class="qa-btn qa-red">
                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    Catat Limbah / Kerusakan
                </a>
                <a href="{{ route('dapur.stok') }}" class="qa-btn qa-green">
                    <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    Lihat Stok Bahan Baku
                </a>
                <a href="{{ route('dapur.notifikasi') }}" class="qa-btn qa-purple">
                    <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    Lihat Notifikasi
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Grafik Stok --}}
<div class="card" style="margin-top:16px">
    <div class="card-head">
        <span class="card-title">Grafik Stok Bahan Baku</span>
        <span style="font-size:12px;color:#6b7280;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:4px 10px">7 Hari Terakhir</span>
    </div>
    <div class="card-body">
        <div style="display:flex;gap:16px;margin-bottom:12px">
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280"><div style="width:10px;height:10px;background:#16a34a;border-radius:2px"></div>Stok Aman</div>
            <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:#6b7280"><div style="width:10px;height:10px;background:#f97316;border-radius:2px"></div>Stok Menipis</div>
        </div>
        <canvas id="dapurChart" height="120"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    const ctx = document.getElementById('dapurChart');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                { label:'Stok Aman',    data: @json($chartAman),    backgroundColor:'#16a34a', borderRadius:{topLeft:4,topRight:4}, borderSkipped:false },
                { label:'Stok Menipis', data: @json($chartMenipis), backgroundColor:'#f97316', borderRadius:{topLeft:4,topRight:4}, borderSkipped:false }
            ]
        },
        options: {
            responsive:true, maintainAspectRatio:true,
            interaction:{ mode:'index', intersect:false },
            plugins:{ legend:{ display:false } },
            scales:{
                x:{ grid:{display:false}, border:{display:false}, ticks:{font:{size:11},color:'#9ca3af'} },
                y:{ grid:{color:'#f3f4f6'}, border:{display:false}, ticks:{stepSize:1,font:{size:11},color:'#9ca3af',callback:v=>Number.isInteger(v)?v:''} }
            }
        }
    });
});
</script>
@endpush
