@extends('layouts.kasir')
@section('title','Menu & Pesanan')

@push('styles')
<style>
/* ── POS Layout ── */
.pos-wrap { display:grid; grid-template-columns:1fr 320px; gap:16px; align-items:start; }

/* ── Search & Filter ── */
.search-bar { display:flex; align-items:center; gap:8px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:0 14px; margin-bottom:14px; }
.search-bar svg { width:16px; height:16px; stroke:#9ca3af; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }
.search-bar input { flex:1; padding:10px 0; border:none; background:transparent; font-size:13px; font-family:inherit; color:#111827; outline:none; }
.search-bar input::placeholder { color:#9ca3af; }

.filter-tabs { display:flex; gap:6px; margin-bottom:14px; flex-wrap:wrap; }
.filter-tab { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:500; cursor:pointer; border:1px solid #e5e7eb; background:#fff; color:#6b7280; transition:all .15s; }
.filter-tab.active, .filter-tab:hover { background:#16a34a; color:#fff; border-color:#16a34a; }

/* ── Menu Grid ── */
.menu-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
.menu-card {
    background:#fff; border-radius:12px; border:2px solid transparent;
    overflow:hidden; cursor:pointer; transition:border-color .15s, transform .15s, box-shadow .15s;
    box-shadow:0 1px 3px rgba(0,0,0,.06);
}
.menu-card:hover { border-color:#16a34a; transform:translateY(-2px); box-shadow:0 6px 18px rgba(22,163,74,.15); }
.menu-card.in-cart { border-color:#16a34a; background:#f0fdf4; }

/* Menu card placeholder (no image) */
.menu-card-placeholder {
    height:80px; display:flex; align-items:center; justify-content:center;
    background:#f9fafb; border-bottom:1px solid #f3f4f6;
    position:relative;
}
.menu-card-placeholder svg { width:28px; height:28px; stroke:#d1d5db; fill:none; stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; }
.menu-card-qty { position:absolute; top:6px; right:6px; background:#16a34a; color:#fff; width:22px; height:22px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
.menu-card-body { padding:10px; }
.menu-card-name { font-size:12px; font-weight:600; color:#111827; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.menu-card-price { font-size:13px; font-weight:700; color:#16a34a; }

/* ── Order Panel ── */
.order-panel { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); position:sticky; top:78px; }
.order-header { padding:14px 16px; border-bottom:1px solid #f3f4f6; display:flex; align-items:center; justify-content:space-between; }
.order-title { font-size:14px; font-weight:700; color:#111827; }
.order-meta  { font-size:11px; color:#9ca3af; margin-top:2px; }
.order-clear { font-size:12px; color:#ef4444; cursor:pointer; background:none; border:none; font-weight:500; display:flex; align-items:center; gap:4px; }
.order-clear svg { width:13px; height:13px; stroke:currentColor; fill:none; stroke-width:2.5; stroke-linecap:round; stroke-linejoin:round; }

.order-items { max-height:300px; overflow-y:auto; }
.order-item  { display:flex; align-items:center; gap:8px; padding:10px 14px; border-bottom:1px solid #f9fafb; }
.oi-info  { flex:1; min-width:0; }
.oi-name  { font-size:12px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.oi-price { font-size:11px; color:#6b7280; }
.oi-qty   { display:flex; align-items:center; gap:5px; flex-shrink:0; }
.qty-btn  { width:28px; height:28px; border-radius:7px; border:1.5px solid #16a34a; background:#f0fdf4; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:16px; font-weight:700; color:#16a34a; transition:background .1s, transform .1s; flex-shrink:0; }
.qty-btn:hover { background:#dcfce7; transform:scale(1.08); }
.qty-btn.del  { border-color:#fca5a5; background:#fff; color:#ef4444; font-size:13px; }
.qty-btn.del:hover { background:#fef2f2; }
.qty-val  { width:40px; text-align:center; font-size:13px; font-weight:700; color:#111827; border:1.5px solid #e5e7eb; border-radius:6px; padding:3px 4px; outline:none; background:#f9fafb; -moz-appearance:textfield; }
.qty-val::-webkit-inner-spin-button, .qty-val::-webkit-outer-spin-button { -webkit-appearance:none; margin:0; }
.qty-val:focus { border-color:#16a34a; background:#fff; }
.oi-total { font-size:12px; font-weight:700; color:#111827; flex-shrink:0; min-width:64px; text-align:right; }

.order-empty { padding:32px 16px; text-align:center; color:#9ca3af; }
.order-empty svg { width:40px; height:40px; stroke:#d1d5db; fill:none; stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; margin:0 auto 8px; display:block; }
.order-empty p { font-size:13px; }

/* ── Summary ── */
.order-summary { padding:14px 16px; border-top:1px solid #f3f4f6; }
.summary-row { display:flex; justify-content:space-between; font-size:13px; color:#6b7280; margin-bottom:6px; }
.summary-row.total { font-size:15px; font-weight:700; color:#111827; border-top:1px solid #e5e7eb; margin-top:8px; padding-top:10px; }
.summary-row.total span:last-child { color:#16a34a; font-size:16px; }

/* ── Process btn ── */
.process-btn {
    width:calc(100% - 32px); margin:0 16px 16px; padding:13px;
    border-radius:10px; background:#16a34a; color:#fff; border:none;
    font-size:14px; font-weight:600; cursor:pointer;
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:background .15s, transform .1s;
}
.process-btn:hover:not(:disabled) { background:#15803d; transform:translateY(-1px); }
.process-btn:disabled { background:#d1d5db; cursor:not-allowed; transform:none; }
.process-btn svg { width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
</style>
@endpush

@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
    <div>
        <h1 style="font-size:18px;font-weight:700;color:#111827;">Selamat Datang, {{ Auth::user()->nama ?? Auth::user()->name }}!</h1>
        <p style="font-size:13px;color:#6b7280;margin-top:2px;">Lakukan transaksi dan setor pendapatan di akhir shift.</p>
    </div>
</div>

{{-- Alerts --}}
@if(session('success'))
<div class="alert alert-success" style="margin-bottom:14px;">
    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="margin-bottom:14px;">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Stats --}}
<div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:16px;">
    <div class="stat-card">
        <div class="stat-icon si-green"><svg viewBox="0 0 24 24"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3v7"/></svg></div>
        <div class="stat-info"><label>Total Menu</label><div class="stat-value">{{ $totalMenu }}</div><div class="stat-unit">tersedia</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><svg viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></div>
        <div class="stat-info"><label>Transaksi Hari Ini</label><div class="stat-value">{{ $transaksiHariIni }}</div><div class="stat-unit">transaksi</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-purple"><svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="stat-info"><label>Pendapatan Hari Ini</label><div class="stat-value" style="font-size:15px;">Rp {{ number_format($pendapatanDisplay,0,',','.') }}</div>
        @if($sudahDiterima)
            <div class="stat-unit" style="color:#16a34a;">✓ sudah diterima admin</div>
        @elseif($sudahSetor)
            <div class="stat-unit" style="color:#f97316;">menunggu konfirmasi</div>
        @else
            <div class="stat-unit">belum disetor</div>
        @endif
        </div>
    </div>
    <div class="stat-card {{ $sudahSetor ? 'border-green' : '' }}" style="{{ $sudahSetor ? 'border:2px solid #bbf7d0;' : '' }}">
        <div class="stat-icon si-orange"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="stat-info"><label>Stok Menipis</label><div class="stat-value">{{ $bahanMenipis }}</div><div class="stat-unit">bahan baku</div></div>
    </div>
</div>

{{-- Setor Pendapatan Banner --}}
@if($sudahDiterima && $pendapatanDisplay > 0)
{{-- Sudah diterima tapi ada transaksi baru lagi --}}
<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
    <div>
        <div style="font-size:13px;font-weight:700;color:#15803d;margin-bottom:2px;">💰 Ada Pendapatan Baru Setelah Setoran</div>
        <div style="font-size:12px;color:#166534;">Transaksi baru: <strong>Rp {{ number_format($pendapatanDisplay,0,',','.') }}</strong> — setor kembali ke admin jika shift sudah selesai.</div>
    </div>
    <button onclick="document.getElementById('setoranModal').style.display='flex'" style="background:#16a34a;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;flex-shrink:0;">Setor Lagi</button>
</div>
@elseif($sudahDiterima)
{{-- Sudah diterima, belum ada transaksi baru --}}
<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 18px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
    <svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:#16a34a;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <span style="font-size:13px;color:#15803d;font-weight:500;">Setoran hari ini sudah <strong>diterima admin</strong>. Pendapatan Anda telah bersih (Rp 0). Terima kasih!</span>
</div>
@elseif(!$sudahSetor && $pendapatanHariIni > 0)
{{-- Belum setor, ada pendapatan --}}
<div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0;border-radius:12px;padding:14px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
    <div>
        <div style="font-size:13px;font-weight:700;color:#15803d;margin-bottom:2px;">💰 Setor Pendapatan Hari Ini</div>
        <div style="font-size:12px;color:#166534;">Total transaksi hari ini: <strong>Rp {{ number_format($pendapatanHariIni,0,',','.') }}</strong> — segera setor ke admin di akhir shift.</div>
    </div>
    <button onclick="document.getElementById('setoranModal').style.display='flex'" style="background:#16a34a;color:#fff;border:none;padding:9px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;flex-shrink:0;">Setor Sekarang</button>
</div>
@elseif($sudahSetor)
{{-- Sudah setor, menunggu konfirmasi --}}
<div style="background:#fff7ed;border:2px solid #f97316;border-radius:12px;padding:16px 18px;margin-bottom:16px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
        <svg viewBox="0 0 24 24" style="width:20px;height:20px;stroke:#f97316;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <span style="font-size:14px;color:#c2410c;font-weight:700;">⏳ Menunggu Konfirmasi Admin</span>
    </div>
    <p style="font-size:13px;color:#92400e;margin:0;">Setoran Anda sedang diproses. <strong>Input pesanan baru dinonaktifkan</strong> sementara untuk menjaga akurasi data pendapatan dan stok. Transaksi akan kembali aktif setelah admin mengkonfirmasi setoran.</p>
</div>
@endif

{{-- POS Layout --}}
<div class="pos-wrap">
    {{-- ── Kiri: Menu ── --}}
    <div>
        <div class="card">
            <div class="card-head"><span class="card-title">Daftar Menu</span></div>
            <div class="card-body">
                {{-- Search --}}
                <form method="GET" id="searchForm">
                    <div class="search-bar">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input name="search" value="{{ $search }}" placeholder="Cari menu..." id="searchInput" autocomplete="off">
                        <input type="hidden" name="kategori" id="kategoriInput" value="{{ $kategori }}">
                    </div>
                </form>

                {{-- Filter Kategori & Filter Status Porsi --}}
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
                    {{-- Kategori Menu --}}
                    <div class="filter-tabs" style="margin-bottom:0;">
                        <button type="button" class="filter-tab {{ $kategori==='semua'?'active':'' }}" onclick="setKategori('semua')">Semua Kategori</button>
                        @foreach($kategoris as $kat)
                            <button type="button" class="filter-tab {{ $kategori===$kat?'active':'' }}" onclick="setKategori('{{ $kat }}')">{{ $kat }}</button>
                        @endforeach
                    </div>

                    {{-- Toggle Status Ketersediaan Porsi --}}
                    <div style="display:flex;gap:6px;background:#f3f4f6;padding:3px;border-radius:20px;">
                        <button type="button" class="filter-tab status-filter-btn active" data-filter="all" onclick="filterStatusPorsi('all', this)" style="padding:4px 12px;font-size:11.5px;border:none;">
                            Semua
                        </button>
                        <button type="button" class="filter-tab status-filter-btn" data-filter="ready" onclick="filterStatusPorsi('ready', this)" style="padding:4px 12px;font-size:11.5px;border:none;color:#15803d;">
                            ✓ Siap Jual (Ready)
                        </button>
                        <button type="button" class="filter-tab status-filter-btn" data-filter="soldout" onclick="filterStatusPorsi('soldout', this)" style="padding:4px 12px;font-size:11.5px;border:none;color:#dc2626;">
                            ✗ Sold Out
                        </button>
                    </div>
                </div>

                @if($menus->isEmpty())
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3v7"/></svg>
                        <h3>Tidak ada menu</h3><p>Coba ubah filter pencarian.</p>
                    </div>
                @else
                    <div class="menu-grid" id="menuGrid" style="{{ $masihPending ? 'pointer-events:none;opacity:0.45;user-select:none;' : '' }}">
                        @foreach($menus as $m)
                        @php
                            $porsiTersedia = $m->alokasiHariIni->porsi_tersedia ?? 0;
                            $isSoldOut     = $porsiTersedia <= 0;
                        @endphp
                        <div class="menu-card {{ $isSoldOut ? 'sold-out' : '' }}" id="card-{{ $m->id }}"
                                style="{{ $isSoldOut ? 'opacity:0.6;cursor:not-allowed;' : '' }}"
                                onclick="{{ ($masihPending || $isSoldOut) ? '' : "addToCart({$m->id}, '" . addslashes($m->nama_menu) . "', {$m->harga}, {$porsiTersedia})" }}">
                            <div class="menu-card-placeholder">
                                <svg viewBox="0 0 24 24"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3v7"/></svg>
                                <div class="menu-card-qty" id="badge-{{ $m->id }}" style="display:none;">0</div>

                                {{-- Badge Sisa Porsi / Sold Out --}}
                                @if($isSoldOut)
                                    <div style="position:absolute;bottom:6px;left:6px;background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:2px 6px;border-radius:4px;">
                                        Sold Out
                                    </div>
                                @else
                                    <div style="position:absolute;bottom:6px;left:6px;background:#15803d;color:#fff;font-size:10px;font-weight:600;padding:2px 6px;border-radius:4px;">
                                        Sisa: {{ $porsiTersedia }} porsi
                                    </div>
                                @endif
                            </div>
                            <div class="menu-card-body">
                                <div class="menu-card-name" title="{{ $m->nama_menu }}">{{ $m->nama_menu }}</div>
                                <div class="menu-card-price">Rp {{ number_format($m->harga,0,',','.') }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($masihPending)
                    <div style="background:#fff7ed;border:1px dashed #f97316;border-radius:10px;padding:20px;text-align:center;margin-top:8px;">
                        <div style="font-size:28px;margin-bottom:6px;">🔒</div>
                        <div style="font-size:13px;font-weight:700;color:#c2410c;">Input pesanan dinonaktifkan</div>
                        <div style="font-size:12px;color:#92400e;margin-top:4px;">Tunggu admin konfirmasi setoran Anda terlebih dahulu.</div>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Pesanan terbaru hari ini --}}
        <div class="card" style="margin-top:16px;">
            <div class="card-head">
                <span class="card-title">Pesanan Terbaru Hari Ini</span>
                <a href="{{ route('kasir.riwayat') }}" style="font-size:12px;color:#16a34a;font-weight:500;display:flex;align-items:center;gap:3px;">Lihat Semua <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><polyline points="9 18 15 12 9 6"/></svg></a>
            </div>
            @if($riwayatTerbaru->isEmpty())
                <div class="empty-state" style="padding:24px;"><p>Belum ada transaksi hari ini</p></div>
            @else
                <table class="data-table">
                    <thead><tr><th>Waktu</th><th>Menu</th><th>Porsi</th><th>Total</th></tr></thead>
                    <tbody>
                        @foreach($riwayatTerbaru as $t)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('H:i') }}</td>
                            <td style="font-weight:500;">{{ $t->menu->nama_menu ?? '-' }}</td>
                            <td>{{ $t->jumlah_porsi }}</td>
                            <td style="font-weight:600;color:#15803d;">Rp {{ number_format($t->total_harga,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- ── Kanan: Order Panel ── --}}
    <div class="order-panel">
        <div class="order-header">
            <div>
                <div class="order-title">Pesanan</div>
                <div class="order-meta">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
            <button class="order-clear" onclick="clearCart()">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                Kosongkan
            </button>
        </div>

        <div class="order-items" id="orderItems"></div>
        <div class="order-empty" id="orderEmpty" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 16px;color:#9ca3af">
            <svg viewBox="0 0 24 24" style="width:40px;height:40px;stroke:#d1d5db;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;margin-bottom:8px"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <p style="font-size:13px">Klik menu untuk menambahkan pesanan</p>
        </div>


        {{-- Stock Warning --}}
        <div id="stockWarning" style="display:none;padding:0 14px 10px;">
            <div class="alert alert-error" style="margin:0;padding:10px 12px;font-size:12px;">
                <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <div id="stockWarningText"></div>
            </div>
        </div>

        <div class="order-summary">
            <div class="summary-row total"><span>Total</span><span id="grandTotal">Rp 0</span></div>
        </div>


        <button class="process-btn" id="processBtn" onclick="{{ $masihPending ? '' : 'prosesTransaksi()' }}" {{ $masihPending ? 'disabled title="Tunggu konfirmasi admin"' : 'disabled' }} style="{{ $masihPending ? 'background:#9ca3af;cursor:not-allowed;' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ $masihPending ? '🔒 Menunggu Konfirmasi Admin' : 'Proses Transaksi' }}
        </button>
    </div>
</div>

{{-- ── Setor Modal ── --}}
<div id="setoranModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:300;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:28px;max-width:420px;width:90%;box-shadow:0 20px 50px rgba(0,0,0,.2);">
        <h2 style="font-size:16px;font-weight:700;color:#111827;margin-bottom:6px;">Setor Pendapatan</h2>
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Total pendapatan shift ini yang akan disetor ke admin:</p>
        <div style="background:#f0fdf4;border-radius:10px;padding:16px;text-align:center;margin-bottom:14px;">
            <div style="font-size:24px;font-weight:800;color:#15803d;">Rp {{ number_format($pendapatanDisplay,0,',','.') }}</div>
            <div style="font-size:12px;color:#6b7280;margin-top:4px;">pendapatan yang akan disetor ke admin</div>
        </div>

        {{-- Peringatan penting --}}
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:10px;padding:12px 14px;margin-bottom:16px;">
            <div style="font-size:12px;font-weight:700;color:#dc2626;margin-bottom:5px;">⚠️ Perhatian sebelum menyetor!</div>
            <ul style="font-size:12px;color:#7f1d1d;margin:0;padding-left:16px;line-height:1.7">
                <li>Setelah disetor, <strong>pesanan baru tidak akan muncul</strong> di dashboard & riwayat sampai admin mengkonfirmasi.</li>
                <li>Pesanan tetap bisa diinput untuk pelanggan, tapi <strong>tidak dihitung</strong> ke pendapatan saat ini.</li>
                <li>Pastikan semua transaksi shift ini sudah benar sebelum menyetor.</li>
            </ul>
        </div>

        <form id="setoranForm" onsubmit="submitSetor(event)">
            @csrf
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <input name="keterangan" id="setoranKeterangan" class="form-control" placeholder="Misal: Shift pagi, tanpa masalah..." maxlength="255">
            </div>
            <div style="display:flex;gap:10px;margin-top:16px;">
                <button type="button" onclick="document.getElementById('setoranModal').style.display='none'" class="btn btn-secondary" style="flex:1;">Batal</button>
                <button type="submit" id="setoranBtn" class="btn btn-primary" style="flex:1;background:#dc2626;">🔒 Ya, Setor Sekarang</button>
            </div>
        </form>
    </div>
</div>


{{-- ── Sukses Modal ── --}}
<div id="successModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:300;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:32px;max-width:360px;width:90%;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.2);">
        <div style="width:64px;height:64px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg viewBox="0 0 24 24" style="width:32px;height:32px;stroke:#16a34a;fill:none;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h2 style="font-size:18px;font-weight:700;color:#111827;margin-bottom:6px;">Transaksi Berhasil!</h2>
        <p id="successMsg" style="font-size:13px;color:#6b7280;margin-bottom:6px;"></p>
        <p style="font-size:20px;font-weight:700;color:#16a34a;margin-bottom:20px;" id="successTotal"></p>
        <button onclick="tutupModal()" style="background:#16a34a;color:#fff;border:none;padding:12px 32px;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;width:100%;">Transaksi Berikutnya</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart   = {};
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function addToCart(id, name, price, maxQty) {
    if (maxQty <= 0) {
        showToast('Menu ini sedang Sold Out / belum dialokasikan dapur!', 'error');
        return;
    }
    if (cart[id]) {
        if (cart[id].qty >= maxQty) {
            showToast('Pesanan sudah mencapai batas porsi tersedia (' + maxQty + ' porsi)', 'error');
            return;
        }
        cart[id].qty++;
    } else {
        cart[id] = { name, price, qty: 1, maxQty: maxQty };
    }
    renderCart();
}

function updateQty(id, delta) {
    // Masih dipakai oleh addToCart internal jika diperlukan
    if (!cart[id]) return;
    cart[id].qty = Math.max(1, cart[id].qty + delta);
    renderCart();
}

function hapusItem(id) {
    delete cart[id];
    renderCart();
}

function setQty(id, inputEl) {
    const raw = inputEl.value;
    if (raw === '') return;
    const val = parseInt(raw);
    if (!cart[id]) return;
    if (isNaN(val) || val < 1) return;
    const max = cart[id].maxQty || 999;
    if (val > max) {
        inputEl.value = max;
        cart[id].qty = max;
        showToast('Maksimal porsi tersedia: ' + max + ' porsi', 'error');
    } else {
        cart[id].qty = val;
    }
    const totalEl = document.getElementById('total-' + id);
    if (totalEl) totalEl.textContent = 'Rp ' + fmt(cart[id].price * cart[id].qty);
    const badge = document.getElementById('badge-' + id);
    if (badge) badge.textContent = cart[id].qty;
    updateTotals(Object.keys(cart).reduce((s, cid) => s + cart[cid].price * cart[cid].qty, 0));
}

function fixQty(id, inputEl) {
    // onblur: jika field kosong atau tidak valid, kembalikan ke nilai cart yang tersimpan
    const val = parseInt(inputEl.value);
    if (!cart[id]) return;
    if (isNaN(val) || val < 1) {
        inputEl.value = cart[id].qty;
    }
}

function updateQtyInput(id, delta) {
    if (!cart[id]) return;
    const inputEl = document.querySelector(`input.qty-val[data-id="${id}"]`);
    const max = cart[id].maxQty || 999;
    const newVal = (cart[id].qty || 1) + delta;
    if (newVal < 1) { 
        hapusItem(id); 
        return; 
    }
    if (newVal > max) {
        showToast('Maksimal porsi tersedia hanya ' + max + ' porsi', 'error');
        return;
    }
    cart[id].qty = newVal;
    if (inputEl) inputEl.value = newVal;
    const totalEl = document.getElementById('total-' + id);
    if (totalEl) totalEl.textContent = 'Rp ' + fmt(cart[id].price * cart[id].qty);
    const badge = document.getElementById('badge-' + id);
    if (badge) badge.textContent = newVal;
    updateTotals(Object.keys(cart).reduce((s, cid) => s + cart[cid].price * cart[cid].qty, 0));
}

function clearCart() { cart = {}; renderCart(); }

function renderCart() {
    const itemsEl  = document.getElementById('orderItems');
    const emptyEl  = document.getElementById('orderEmpty');
    const btn      = document.getElementById('processBtn');
    const ids      = Object.keys(cart);

    document.querySelectorAll('.menu-card').forEach(card => {
        const cid   = card.id.replace('card-', '');
        const badge = document.getElementById('badge-' + cid);
        if (!badge) return;
        if (cart[cid]) { badge.style.display = 'flex'; badge.textContent = cart[cid].qty; card.classList.add('in-cart'); }
        else           { badge.style.display = 'none'; card.classList.remove('in-cart'); }
    });

    if (!ids.length) {
        itemsEl.innerHTML = '';   // kosongkan order items
        emptyEl.style.display = 'flex'; // tampilkan empty state (tetap di luar itemsEl)
        btn.disabled = true;
        updateTotals(0);
        return;
    }

    emptyEl.style.display = 'none';
    let html = '';
    ids.forEach(id => {
        const item = cart[id];
        html += `<div class="order-item">
            <div class="oi-info">
                <div class="oi-name">${item.name}</div>
                <div class="oi-price">Rp ${fmt(item.price)} / porsi</div>
            </div>
            <div class="oi-qty">
                <button class="qty-btn del" onclick="updateQtyInput(${id}, -1)" title="Kurangi 1">−</button>
                <input class="qty-val" type="number" min="1" value="${item.qty}"
                    data-id="${id}"
                    oninput="setQty(${id}, this)"
                    onblur="fixQty(${id}, this)"
                    title="Ketik jumlah porsi">
                <button class="qty-btn" onclick="updateQtyInput(${id}, 1)" title="Tambah 1 porsi">+</button>
                <button class="qty-btn del" onclick="hapusItem(${id})" title="Hapus item" style="font-size:16px">×</button>
            </div>
            <div class="oi-total" id="total-${id}">Rp ${fmt(item.price*item.qty)}</div>
        </div>`;
    });
    itemsEl.innerHTML = html;
    btn.disabled = false;

    updateTotals(ids.reduce((s, id) => s + cart[id].price * cart[id].qty, 0));
}

function updateTotals(sub) {
    document.getElementById('grandTotal').textContent = 'Rp ' + fmt(sub);
}


function fmt(n) { return n.toLocaleString('id-ID'); }

async function prosesTransaksi() {
    const ids = Object.keys(cart);
    if (!ids.length) return;

    const btn = document.getElementById('processBtn');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    try {
        const res = await fetch('{{ route("kasir.proses") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ items: ids.map(id => ({ menu_id: parseInt(id), porsi: cart[id].qty })), _token: csrf })
        });
        const data = await res.json();

        document.getElementById('stockWarning').style.display = 'none';

        if (data.success) {
            const sub   = ids.reduce((s, id) => s + cart[id].price * cart[id].qty, 0);
            document.getElementById('successMsg').textContent  = data.no_transaksi + ' · ' + ids.length + ' jenis menu';
            document.getElementById('successTotal').textContent = 'Total: Rp ' + fmt(sub);
            document.getElementById('successModal').style.display = 'flex';
            clearCart();
            setTimeout(() => location.reload(), 6000);
        } else {
            const w = document.getElementById('stockWarning');
            document.getElementById('stockWarningText').innerHTML = data.errors.join('<br>');
            w.style.display = 'block';
            w.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    } catch (e) {
        console.error(e);
        showToast('Gagal memproses transaksi. Coba lagi.', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg viewBox="0 0 24 24" style="width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Proses Transaksi';
    }
}

async function submitSetor(e) {
    e.preventDefault();
    const btn = document.getElementById('setoranBtn');
    const keterangan = document.getElementById('setoranKeterangan').value;
    btn.disabled = true;
    btn.textContent = 'Menyetor...';
    try {
        const res = await fetch('{{ route("kasir.setor") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ keterangan, _token: csrf })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('setoranModal').style.display = 'none';
            showToast(data.message || 'Setoran berhasil!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Gagal melakukan setoran.', 'error');
            btn.disabled = false;
            btn.textContent = '✓ Konfirmasi Setor';
        }
    } catch (err) {
        console.error(err);
        showToast('Gagal menghubungi server. Periksa koneksi.', 'error');
        btn.disabled = false;
        btn.textContent = '✓ Konfirmasi Setor';
    }
}

function tutupModal() { document.getElementById('successModal').style.display = 'none'; }

function setKategori(kat) {
    document.getElementById('kategoriInput').value = kat;
    document.getElementById('searchForm').submit();
}

function filterStatusPorsi(status, btnEl) {
    // Update active class pada tombol filter status
    document.querySelectorAll('.status-filter-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.style.background = 'transparent';
        btn.style.color = '#6b7280';
    });

    btnEl.classList.add('active');
    if (status === 'ready') {
        btnEl.style.background = '#16a34a';
        btnEl.style.color = '#fff';
    } else if (status === 'soldout') {
        btnEl.style.background = '#dc2626';
        btnEl.style.color = '#fff';
    } else {
        btnEl.style.background = '#16a34a';
        btnEl.style.color = '#fff';
    }

    // Filter kartu menu
    const cards = document.querySelectorAll('.menu-card');
    cards.forEach(card => {
        const isSoldOut = card.classList.contains('sold-out');
        if (status === 'all') {
            card.style.display = '';
        } else if (status === 'ready') {
            card.style.display = isSoldOut ? 'none' : '';
        } else if (status === 'soldout') {
            card.style.display = isSoldOut ? '' : 'none';
        }
    });
}

let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => document.getElementById('searchForm').submit(), 400);
});
</script>
@endpush
