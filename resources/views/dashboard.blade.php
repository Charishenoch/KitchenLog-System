@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-hd">
    <div>
        <h1>Selamat Datang, {{ Auth::user()->nama ?? Auth::user()->name }}!</h1>
        <p>Berikut ringkasan kondisi inventaris dan aktivitas restoran hari ini.</p>
    </div>
    <div class="page-date" style="display:flex;align-items:center;gap:7px;font-size:13px;color:#6b7280;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:7px 14px;">
        <svg viewBox="0 0 24 24" style="width:14px;height:14px;stroke:#6b7280;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
    </div>
</div>

{{-- ── Stat cards ── --}}
<div class="stats-row" style="grid-template-columns:repeat(5,1fr);">
    <div class="stat-card">
        <div class="stat-icon-box si-teal">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <div class="stat-info">
            <label>Total Bahan Baku</label>
            <div class="stat-value">{{ $totalBahan }}</div>
            <div class="stat-unit">jenis bahan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-orange">
            <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div class="stat-info">
            <label>Bahan Stok Menipis</label>
            <div class="stat-value">{{ $bahanMenipis }}</div>
            <div class="stat-unit">jenis bahan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-green">
            <svg viewBox="0 0 24 24"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3v7"/></svg>
        </div>
        <div class="stat-info">
            <label>Total Menu</label>
            <div class="stat-value">{{ $totalMenu }}</div>
            <div class="stat-unit">menu tersedia</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-purple">
            <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div class="stat-info">
            <label>Transaksi Hari Ini</label>
            <div class="stat-value">{{ $totalTransaksi }}</div>
            <div class="stat-unit">transaksi</div>
        </div>
    </div>
    <div class="stat-card" style="border:2px solid #bbf7d0;">
        <div class="stat-icon-box si-green">
            <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-info">
            <label>Total Pendapatan Kasir</label>
            <div class="stat-value" style="font-size:14px;">Rp {{ number_format($totalSetoran,0,',','.') }}</div>
            @if($setoranPending > 0)
            <div class="stat-unit" style="color:#f97316;">{{ $setoranPending }} setoran pending</div>
            @else
            <div class="stat-unit">dari setoran kasir</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Grid ── --}}
<div style="display:grid;grid-template-columns:1fr 1fr 268px;grid-template-rows:auto auto;gap:16px;">

    {{-- Bar chart: Stok Bahan Baku --}}
    <div class="card" style="grid-column:1;grid-row:1;">
        <div class="card-head">
            <span class="card-title">Grafik Stok Bahan Baku</span>
            <span style="font-size:12px;color:#6b7280;background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;padding:4px 10px;">10 Bahan Terendah</span>
        </div>
        <div class="card-body">
            <div style="display:flex;gap:14px;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:#6b7280;"><div style="width:10px;height:10px;background:#16a34a;border-radius:2px"></div>Stok Aman</div>
                <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:#6b7280;"><div style="width:10px;height:10px;background:#f97316;border-radius:2px"></div>Stok Menipis</div>
            </div>
            <canvas id="barChart" height="220"></canvas>
        </div>
    </div>

    {{-- Donut chart --}}
    <div class="card" style="grid-column:2;grid-row:1;">
        <div class="card-head"><span class="card-title">Kategori Bahan Baku</span></div>
        <div class="card-body">
            <div style="position:relative;height:170px;display:flex;align-items:center;justify-content:center;">
                <canvas id="donutChart"></canvas>
                <div style="position:absolute;text-align:center;pointer-events:none;">
                    <div style="font-size:22px;font-weight:700;color:#111827;line-height:1;">{{ $totalBahan }}</div>
                    <div style="font-size:10px;color:#9ca3af;">jenis bahan</div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:7px;margin-top:12px;">
                @php $donutColors = ['#22c55e','#3b82f6','#f59e0b','#f97316','#a78bfa']; $i=0; @endphp
                @foreach($kategoriData as $kat => $jml)
                <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;">
                    <div style="display:flex;align-items:center;gap:7px;color:#374151;">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $donutColors[$i] }};flex-shrink:0;"></div>
                        {{ $kat }}
                    </div>
                    <div style="color:#6b7280;font-weight:500;">{{ $jml }} ({{ $totalBahan > 0 ? round($jml/$totalBahan*100) : 0 }}%)</div>
                </div>
                @php $i++; @endphp
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div style="grid-column:3;grid-row:1/3;display:flex;flex-direction:column;gap:16px;">
        {{-- Notifikasi stok --}}
        <div class="card">
            <div class="card-head">
                <div class="card-head-left">
                    <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:#f97316;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span class="card-title">Notifikasi Stok Minimum</span>
                </div>
                <a href="{{ route('notifikasi.index') }}" class="card-link" style="font-size:12px;color:#16a34a;text-decoration:none;font-weight:500;">Lihat Semua</a>
            </div>
            @if($notifStok->isEmpty())
                <div class="empty-state" style="padding:24px;">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p>Semua stok aman</p>
                </div>
            @else
                @foreach($notifStok as $b)
                <div style="display:flex;align-items:center;gap:9px;padding:10px 14px;border-bottom:1px solid #f3f4f6;">
                    <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#f97316;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:600;color:#111827;">{{ $b->nama_bahan }}</div>
                        <div style="font-size:11px;color:#6b7280;">Sisa {{ $b->stok }} {{ $b->satuan }} (Min. {{ $b->batas_minimum }})</div>
                    </div>
                    <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:#d1d5db;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Aksi Cepat --}}
        <div class="card">
            <div class="card-head"><span class="card-title">Aksi Cepat</span></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px;">
                <a href="{{ route('bahan-baku.create') }}" style="display:flex;flex-direction:column;align-items:center;gap:7px;padding:16px 10px;border-radius:10px;background:#ccfbf1;color:#0d9488;font-size:11.5px;font-weight:600;text-align:center;text-decoration:none;">
                    <svg viewBox="0 0 24 24" style="width:26px;height:26px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    Tambah Bahan Baku
                </a>
                <a href="{{ route('menu.create') }}" style="display:flex;flex-direction:column;align-items:center;gap:7px;padding:16px 10px;border-radius:10px;background:#dcfce7;color:#16a34a;font-size:11.5px;font-weight:600;text-align:center;text-decoration:none;">
                    <svg viewBox="0 0 24 24" style="width:26px;height:26px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3v7"/></svg>
                    Tambah Menu
                </a>
                <a href="{{ route('laporan.index') }}" style="display:flex;flex-direction:column;align-items:center;gap:7px;padding:16px 10px;border-radius:10px;background:#f3e8ff;color:#9333ea;font-size:11.5px;font-weight:600;text-align:center;text-decoration:none;">
                    <svg viewBox="0 0 24 24" style="width:26px;height:26px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Lihat Laporan
                </a>
                <a href="{{ route('pengguna.index') }}" style="display:flex;flex-direction:column;align-items:center;gap:7px;padding:16px 10px;border-radius:10px;background:#fff7ed;color:#ea580c;font-size:11.5px;font-weight:600;text-align:center;text-decoration:none;">
                    <svg viewBox="0 0 24 24" style="width:26px;height:26px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Kelola Pengguna
                </a>
            </div>
        </div>
    </div>

    {{-- Stok terendah --}}
    <div class="card" style="grid-column:1;grid-row:2;">
        <div class="card-head">
            <span class="card-title">Stok Bahan Baku Terendah</span>
            <a href="{{ route('bahan-baku.index') }}" class="card-link" style="font-size:12px;color:#16a34a;text-decoration:none;font-weight:500;display:flex;align-items:center;gap:3px;">Lihat Semua <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><polyline points="9 18 15 12 9 6"/></svg></a>
        </div>
        @if($stokTerendah->isEmpty())
            <div class="empty-state"><p>Belum ada data bahan baku</p></div>
        @else
        <table class="data-table">
            <thead><tr><th>No</th><th>Nama Bahan</th><th>Stok Tersisa</th><th>Satuan</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($stokTerendah as $i => $b)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $b->nama_bahan }}</td>
                    <td>{{ $b->stok }}</td>
                    <td>{{ $b->satuan }}</td>
                    <td>
                        @if($b->stok <= $b->batas_minimum)
                            <span class="badge badge-orange">Stok Menipis</span>
                        @else
                            <span class="badge badge-green">Aman</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Aktivitas terbaru --}}
    <div class="card" style="grid-column:2;grid-row:2;">
        <div class="card-head">
            <span class="card-title">Aktivitas Terbaru</span>
            <a href="{{ route('laporan.index') }}" class="card-link" style="font-size:12px;color:#16a34a;text-decoration:none;font-weight:500;">Lihat Semua</a>
        </div>
        @if($aktivitas->isEmpty())
            <div class="empty-state"><p>Belum ada transaksi hari ini</p></div>
        @else
            @foreach($aktivitas as $trx)
            <div style="display:flex;align-items:flex-start;gap:11px;padding:12px 18px;border-bottom:1px solid #f3f4f6;">
                <div style="width:36px;height:36px;border-radius:9px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $trx->menu->nama_menu ?? '-' }}</div>
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">Kasir: {{ $trx->kasir->nama ?? '-' }} · Rp {{ number_format($trx->total_harga,0,',','.') }}</div>
                </div>
                <span style="font-size:11px;color:#9ca3af;white-space:nowrap;flex-shrink:0;">{{ \Carbon\Carbon::parse($trx->tanggal)->diffForHumans() }}</span>
            </div>
            @endforeach
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var barEl = document.getElementById('barChart');
    if (barEl && typeof Chart !== 'undefined') {
        var labels       = @json($labels);
        var dataAman     = @json($chartAman);
        var dataMenipis  = @json($chartPendapatan);

        new Chart(barEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Stok Aman',
                        data: dataAman,
                        backgroundColor: '#16a34a',
                        borderRadius: { topLeft: 4, topRight: 4 },
                        borderSkipped: false
                    },
                    {
                        label: 'Stok Menipis',
                        data: dataMenipis,
                        backgroundColor: '#f97316',
                        borderRadius: { topLeft: 4, topRight: 4 },
                        borderSkipped: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + ctx.parsed.y + ' unit';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            font: { size: 10 },
                            color: '#9ca3af',
                            maxRotation: 35,
                            minRotation: 20
                        }
                    },
                    y: {
                        grid: { color: '#f3f4f6' },
                        border: { display: false },
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 },
                            color: '#9ca3af',
                            callback: function(v) { return Number.isInteger(v) ? v : ''; }
                        }
                    }
                }
            }
        });
    }

    // ── Donut chart: Kategori bahan baku ──
    var donutEl = document.getElementById('donutChart');
    if (donutEl && typeof Chart !== 'undefined') {
        new Chart(donutEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: @json(array_keys($kategoriData)),
                datasets: [{ data: @json(array_values($kategoriData)), backgroundColor: ['#22c55e','#3b82f6','#f59e0b','#f97316','#a78bfa'], borderWidth: 3, borderColor: '#fff', hoverOffset: 5 }]
            },
            options: { cutout: '68%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    }
});
</script>
@endpush


