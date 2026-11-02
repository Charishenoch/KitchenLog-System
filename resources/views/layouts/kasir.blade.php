<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KasirLog') — KitchenLog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',system-ui,sans-serif; background:#f1f5f9; color:#111827; font-size:14px; }
        a { text-decoration:none; color:inherit; }

        /* ── App shell ── */
        .app { display:flex; min-height:100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width:220px; min-width:220px; background:#fff;
            border-right:1px solid #e5e7eb;
            display:flex; flex-direction:column;
            position:fixed; top:0; left:0; bottom:0; z-index:100; overflow-y:auto;
        }
        .sb-logo { padding:14px; display:flex; align-items:center; gap:10px; border-bottom:1px solid #e5e7eb; min-height:62px; }
        .sb-icon  { width:36px; height:36px; background:#16a34a; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .sb-icon svg { width:20px; height:20px; }
        .sb-text h2 { font-size:13px; font-weight:700; color:#16a34a; }
        .sb-text p  { font-size:10px; color:#6b7280; }

        .sb-role { margin:10px 10px 4px; padding:8px 10px; background:#f0fdf4; border-radius:8px; border:1px solid #bbf7d0; }
        .sb-role-label { font-size:10px; color:#6b7280; font-weight:500; }
        .sb-role-name  { font-size:12px; font-weight:700; color:#15803d; }

        .sb-nav { flex:1; padding:8px; display:flex; flex-direction:column; gap:1px; }
        .nav-item {
            display:flex; align-items:center; gap:9px;
            padding:9px 11px; border-radius:8px;
            color:#374151; font-size:13px; font-weight:500;
            transition:background .12s, color .12s;
            cursor:pointer; border:none; background:none; width:100%; text-align:left;
        }
        .nav-item:hover { background:#f3f4f6; color:#16a34a; }
        .nav-item.active { background:#16a34a; color:#fff; }
        .nav-item svg { width:16px; height:16px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .nav-badge { margin-left:auto; background:#ef4444; color:#fff; font-size:10px; font-weight:700; padding:1px 6px; border-radius:10px; }
        .nav-item.logout-btn { color:#ef4444; }
        .nav-item.logout-btn:hover { background:#fef2f2; }
        .sb-bottom { padding:8px; border-top:1px solid #e5e7eb; }

        /* ── Main ── */
        .main-wrapper { margin-left:220px; flex:1; display:flex; flex-direction:column; min-height:100vh; }

        /* ── Topbar ── */
        .topbar {
            height:58px; background:#fff; border-bottom:1px solid #e5e7eb;
            display:flex; align-items:center; justify-content:space-between;
            padding:0 22px; position:sticky; top:0; z-index:50;
        }
        .topbar-left { display:flex; align-items:center; gap:14px; }
        .topbar-date  { display:flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; }
        .topbar-date svg { width:14px; height:14px; stroke:#9ca3af; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .topbar-right { display:flex; align-items:center; gap:16px; }
        .topbar-bell { position:relative; cursor:pointer; display:flex; align-items:center; }
        .topbar-bell svg { width:20px; height:20px; stroke:#6b7280; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .bell-dot { position:absolute; top:0; right:-1px; width:8px; height:8px; background:#ef4444; border-radius:50%; border:2px solid #fff; }
        .topbar-user { display:flex; align-items:center; gap:9px; cursor:pointer; }
        .topbar-avatar { width:34px; height:34px; border-radius:50%; background:#16a34a; color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:600; overflow:hidden; flex-shrink:0; }
        .topbar-avatar img { width:100%; height:100%; object-fit:cover; }
        .topbar-user-info h3 { font-size:13px; font-weight:600; color:#111827; }
        .topbar-user-info p  { font-size:11px; color:#6b7280; }

        /* ── Content ── */
        .content { padding:20px 22px; flex:1; }
        .page-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
        .page-hd h1 { font-size:18px; font-weight:700; color:#111827; }
        .page-hd p  { font-size:13px; color:#6b7280; margin-top:2px; }

        /* ── Shared UI ── */
        .card { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.06); overflow:hidden; }
        .card-head { display:flex; align-items:center; justify-content:space-between; padding:13px 16px; border-bottom:1px solid #f3f4f6; }
        .card-title { font-size:13px; font-weight:600; color:#111827; }
        .card-body  { padding:16px; }

        .btn { display:inline-flex; align-items:center; gap:7px; padding:8px 16px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer; border:none; transition:opacity .15s, transform .12s; }
        .btn:hover { opacity:.88; transform:translateY(-1px); }
        .btn svg { width:15px; height:15px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .btn-primary   { background:#16a34a; color:#fff; }
        .btn-secondary { background:#f3f4f6; color:#374151; }
        .btn-danger    { background:#fee2e2; color:#dc2626; }
        .btn-sm { padding:5px 11px; font-size:12px; }

        .badge { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11px; font-weight:500; }
        .badge-green  { background:#dcfce7; color:#15803d; }
        .badge-orange { background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; }
        .badge-red    { background:#fee2e2; color:#dc2626; }
        .badge-blue   { background:#dbeafe; color:#1d4ed8; }
        .badge-gray   { background:#f3f4f6; color:#6b7280; }

        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { text-align:left; padding:10px 14px; font-size:12px; font-weight:600; color:#6b7280; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
        .data-table td { padding:11px 14px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6; }
        .data-table tbody tr:last-child td { border-bottom:none; }
        .data-table tbody tr:hover { background:#fafafa; }

        .alert { padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; display:flex; align-items:flex-start; gap:10px; }
        .alert svg { width:16px; height:16px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; margin-top:1px; }
        .alert-success { background:#dcfce7; border:1px solid #86efac; color:#15803d; }
        .alert-error   { background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; }
        .alert-warning { background:#fff7ed; border:1px solid #fed7aa; color:#ea580c; }

        .stats-row { display:grid; gap:14px; margin-bottom:18px; }
        .stats-row-4 { grid-template-columns:repeat(4,1fr); }
        .stat-card { background:#fff; border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
        .stat-icon { width:46px; height:46px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .stat-icon svg { width:22px; height:22px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .si-green  { background:#dcfce7; color:#16a34a; }
        .si-blue   { background:#dbeafe; color:#2563eb; }
        .si-purple { background:#f3e8ff; color:#9333ea; }
        .si-orange { background:#fff7ed; color:#f97316; }
        .stat-info label { font-size:11px; color:#6b7280; font-weight:500; display:block; }
        .stat-value { font-size:22px; font-weight:700; color:#111827; line-height:1.2; }
        .stat-unit  { font-size:11px; color:#9ca3af; }

        .form-control { width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; font-family:inherit; color:#111827; background:#fff; transition:border-color .15s, box-shadow .15s; }
        .form-control:focus { outline:none; border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.1); }

        .pagination-wrap { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid #f3f4f6; font-size:12px; color:#6b7280; }

        .empty-state { text-align:center; padding:44px 20px; color:#9ca3af; }
        .empty-state svg { width:44px; height:44px; stroke:#d1d5db; fill:none; stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; margin:0 auto 10px; }
        .empty-state h3 { font-size:15px; font-weight:600; color:#6b7280; margin-bottom:4px; }

        /* ── Toast ── */
        #toast { position:fixed; bottom:24px; right:24px; z-index:999; display:flex; flex-direction:column; gap:8px; }
        .toast-item { padding:14px 18px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(0,0,0,.15); min-width:280px; display:flex; align-items:center; gap:10px; animation:slideIn .3s ease; }
        .toast-item svg { width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }
        .toast-success { background:#16a34a; color:#fff; }
        .toast-error   { background:#dc2626; color:#fff; }
        @keyframes slideIn { from { transform:translateX(100px); opacity:0; } to { transform:translateX(0); opacity:1; } }

        @media (max-width:1100px) { .stats-row-4 { grid-template-columns:repeat(2,1fr); } }
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    {{-- ══ SIDEBAR ══ --}}
    <aside class="sidebar">
        <div class="sb-logo">
            <div class="sb-icon">
                <svg viewBox="0 0 64 64" fill="none"><path d="M32 8c-4.4 0-8 3.6-8 8 0-.5-3.6-4-8-4-5.5 0-10 4.5-10 10 0 4.8 3.4 8.8 8 9.8V48h36V31.8c4.6-1 8-5 8-9.8 0-5.5-4.5-10-10-10-4.4 0-7.5 3.5-8 4 0-4.4-3.6-8-8-8z" fill="white" opacity=".95"/><rect x="16" y="48" width="32" height="8" rx="2" fill="white"/></svg>
            </div>
            <div class="sb-text"><h2>KitchenLog</h2><p>Sistem Manajemen Inventaris</p></div>
        </div>

        @php
            $notifCount = $notifCount ?? \App\Models\Notifikasi::where('status','belum_dibaca')->count();
            $cr = request()->route()->getName();
        @endphp

        <div class="sb-role">
            <div class="sb-role-label">Role Aktif</div>
            <div class="sb-role-name">
                <svg viewBox="0 0 24 24" style="width:12px;height:12px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;vertical-align:middle;margin-right:3px;"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                Kasir
            </div>
        </div>

        <nav class="sb-nav">
            <a href="{{ route('kasir.dashboard') }}" class="nav-item {{ $cr==='kasir.dashboard'?'active':'' }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Menu &amp; Pesanan
            </a>
            <a href="{{ route('kasir.riwayat') }}" class="nav-item {{ $cr==='kasir.riwayat'?'active':'' }}">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Riwayat Transaksi
            </a>
            <a href="{{ route('kasir.stok') }}" class="nav-item {{ $cr==='kasir.stok'?'active':'' }}">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Stok Bahan
            </a>
            <a href="{{ route('kasir.notifikasi') }}" class="nav-item {{ $cr==='kasir.notifikasi'?'active':'' }}">
                <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifikasi
                @if($notifCount>0)<span class="nav-badge">{{ $notifCount }}</span>@endif
            </a>
        </nav>

        <div class="sb-bottom">
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="nav-item logout-btn">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ══ MAIN ══ --}}
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-date">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </div>
                <div class="topbar-date">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span id="jamSekarang">{{ \Carbon\Carbon::now()->format('H:i') }}</span> WIB
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('kasir.notifikasi') }}" class="topbar-bell">
                    <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    @if($notifCount>0)<span class="bell-dot"></span>@endif
                </a>
                <div class="topbar-user">
                    <div class="topbar-avatar">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="">
                        @else
                            {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'K', 0, 1)) }}
                        @endif
                    </div>
                    <div class="topbar-user-info">
                        <h3>{{ Auth::user()->nama ?? Auth::user()->name }}</h3>
                        <p>Kasir</p>
                    </div>
                </div>
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    {!! session('error') !!}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

{{-- Toast container --}}
<div id="toast"></div>

<script>
// Live clock
(function updateClock() {
    const el = document.getElementById('jamSekarang');
    if (el) {
        const now = new Date();
        el.textContent = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
    }
    setTimeout(updateClock, 10000);
})();

// Toast helper
window.showToast = function(message, type='success') {
    const t = document.createElement('div');
    t.className = 'toast-item toast-' + type;
    t.innerHTML = type === 'success'
        ? `<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span>${message}</span>`
        : `<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><span>${message}</span>`;
    document.getElementById('toast').appendChild(t);
    setTimeout(() => t.remove(), 4000);
};
</script>
@stack('scripts')
</body>
</html>
