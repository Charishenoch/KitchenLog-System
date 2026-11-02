<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KitchenLog') - KitchenLog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', system-ui, sans-serif; background: #f1f5f9; color: #111827; font-size: 14px; }
        a { text-decoration: none; color: inherit; }

        /* ─── App Layout ─── */
        .app { display: flex; min-height: 100vh; }

        /* ─── Sidebar ─── */
        .sidebar {
            width: 220px; min-width: 220px;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0;
            z-index: 100; overflow-y: auto;
        }
        .sidebar-logo {
            padding: 14px; display: flex; align-items: center; gap: 10px;
            border-bottom: 1px solid #e5e7eb; min-height: 62px;
        }
        .logo-icon {
            width: 36px; height: 36px; background: #16a34a;
            border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .logo-icon svg { width: 20px; height: 20px; }
        .logo-text h2 { font-size: 13px; font-weight: 700; color: #16a34a; line-height: 1.2; }
        .logo-text p  { font-size: 10px; color: #6b7280; }

        .sidebar-nav { flex: 1; padding: 8px; display: flex; flex-direction: column; gap: 1px; }

        .nav-item {
            display: flex; align-items: center; gap: 9px;
            padding: 9px 11px; border-radius: 8px;
            color: #374151; font-size: 13px; font-weight: 500;
            transition: background .12s, color .12s;
            cursor: pointer; border: none; background: none; width: 100%; text-align: left;
        }
        .nav-item:hover { background: #f3f4f6; color: #16a34a; }
        .nav-item.active { background: #16a34a; color: #fff; }
        .nav-item.active:hover { background: #15803d; }
        .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .nav-badge { margin-left: auto; background: #ef4444; color: #fff; font-size: 10px; font-weight: 700; padding: 1px 6px; border-radius: 10px; }
        .nav-chevron { margin-left: auto; width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .nav-item.logout-btn { color: #ef4444; }
        .nav-item.logout-btn:hover { background: #fef2f2; }
        .sidebar-bottom { padding: 8px; border-top: 1px solid #e5e7eb; }

        /* ─── Main ─── */
        .main-wrapper { margin-left: 220px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ─── Topbar ─── */
        .topbar {
            height: 58px; background: #fff; border-bottom: 1px solid #e5e7eb;
            display: flex; align-items: center; justify-content: flex-end;
            padding: 0 22px; gap: 16px; position: sticky; top: 0; z-index: 50;
        }
        .topbar-bell { position: relative; cursor: pointer; display: flex; align-items: center; }
        .topbar-bell svg { width: 20px; height: 20px; stroke: #6b7280; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .bell-dot { position: absolute; top: 0; right: -1px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #fff; }
        .topbar-user { display: flex; align-items: center; gap: 9px; cursor: pointer; }
        .topbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: #16a34a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; overflow: hidden; flex-shrink: 0; }
        .topbar-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .topbar-user-info h3 { font-size: 13px; font-weight: 600; color: #111827; }
        .topbar-user-info p  { font-size: 11px; color: #6b7280; }
        .topbar-user svg { width: 14px; height: 14px; stroke: #9ca3af; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Content ─── */
        .content { padding: 22px 22px; flex: 1; }

        /* ─── Page header ─── */
        .page-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .page-hd h1 { font-size: 18px; font-weight: 700; color: #111827; }
        .page-hd p  { font-size: 13px; color: #6b7280; margin-top: 2px; }

        /* ─── Card ─── */
        .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
        .card-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #f3f4f6; }
        .card-head-left { display: flex; align-items: center; gap: 8px; }
        .card-title { font-size: 13px; font-weight: 600; color: #111827; }
        .card-body  { padding: 16px 18px; }

        /* ─── Buttons ─── */
        .btn { display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; transition: opacity .15s, transform .12s; }
        .btn:hover { opacity: .88; transform: translateY(-1px); }
        .btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .btn-primary { background: #16a34a; color: #fff; }
        .btn-secondary { background: #f3f4f6; color: #374151; }
        .btn-danger  { background: #fee2e2; color: #dc2626; }
        .btn-warning { background: #fff7ed; color: #ea580c; }
        .btn-sm { padding: 5px 11px; font-size: 12px; }
        .btn-sm svg { width: 13px; height: 13px; }

        /* ─── Table ─── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 10px 14px; font-size: 12px; font-weight: 600; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
        .data-table td { padding: 11px 14px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover { background: #fafafa; }
        .actions-cell { display: flex; gap: 6px; }

        /* ─── Badges ─── */
        .badge { display: inline-block; padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 500; }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-orange { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
        .badge-red    { background: #fee2e2; color: #dc2626; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-gray   { background: #f3f4f6; color: #6b7280; }
        .badge-purple { background: #f3e8ff; color: #7e22ce; }

        /* ─── Form ─── */
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .form-grid-1 { grid-template-columns: 1fr; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 13px; font-weight: 500; color: #374151; }
        .form-control {
            width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px;
            font-size: 13px; font-family: inherit; color: #111827; background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22,163,74,.1); }
        select.form-control { cursor: pointer; }
        textarea.form-control { resize: vertical; min-height: 80px; }
        .form-hint { font-size: 11px; color: #9ca3af; }
        .form-actions { display: flex; gap: 10px; margin-top: 6px; }

        /* ─── Alert / Flash ─── */
        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
        .alert svg { width: 16px; height: 16px; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .alert-success { background: #dcfce7; border: 1px solid #86efac; color: #15803d; }
        .alert-error   { background: #fee2e2; border: 1px solid #fca5a5; color: #dc2626; }
        .alert-warning { background: #fff7ed; border: 1px solid #fed7aa; color: #ea580c; }

        /* ─── Empty state ─── */
        .empty-state { text-align: center; padding: 48px 20px; color: #9ca3af; }
        .empty-state svg { width: 48px; height: 48px; stroke: #d1d5db; fill: none; stroke-width: 1.5; stroke-linecap: round; stroke-linejoin: round; margin: 0 auto 12px; }
        .empty-state h3 { font-size: 15px; font-weight: 600; color: #6b7280; margin-bottom: 4px; }
        .empty-state p  { font-size: 13px; }

        /* ─── Stats ─── */
        .stats-row { display: grid; gap: 14px; margin-bottom: 18px; }
        .stats-row-4 { grid-template-columns: repeat(4, 1fr); }
        .stats-row-3 { grid-template-columns: repeat(3, 1fr); }
        .stat-card { background: #fff; border-radius: 12px; padding: 18px; display: flex; align-items: center; gap: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
        .stat-icon-box { width: 48px; height: 48px; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .stat-icon-box svg { width: 24px; height: 24px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
        .si-teal   { background: #ccfbf1; color: #0d9488; }
        .si-orange { background: #fff7ed; color: #f97316; }
        .si-green  { background: #dcfce7; color: #16a34a; }
        .si-purple { background: #f3e8ff; color: #9333ea; }
        .si-blue   { background: #dbeafe; color: #2563eb; }
        .si-red    { background: #fee2e2; color: #dc2626; }
        .stat-info label { font-size: 11px; color: #6b7280; font-weight: 500; display: block; }
        .stat-value { font-size: 24px; font-weight: 700; color: #111827; line-height: 1.1; }
        .stat-unit  { font-size: 11px; color: #9ca3af; }

        /* ─── Modal ─── */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 200; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal { background: #fff; border-radius: 14px; padding: 24px; max-width: 480px; width: 94%; box-shadow: 0 20px 50px rgba(0,0,0,.15); }
        .modal-title { font-size: 16px; font-weight: 700; margin-bottom: 16px; }
        .modal-actions { display: flex; gap: 10px; margin-top: 18px; justify-content: flex-end; }

        /* ─── Pagination ─── */
        .pagination-wrap { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-top: 1px solid #f3f4f6; font-size: 12px; color: #6b7280; }
        .pagination-btns { display: flex; gap: 4px; }
        .page-btn { width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 6px; border: 1px solid #e5e7eb; background: #fff; color: #374151; font-size: 12px; cursor: pointer; }
        .page-btn.active { background: #16a34a; color: #fff; border-color: #16a34a; }
        .page-btn svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }

        /* ─── Responsive tweaks ─── */
        @media (max-width: 1100px) {
            .stats-row-4 { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    {{-- ═══════ SIDEBAR ═══════ --}}
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <svg viewBox="0 0 64 64" fill="none">
                    <path d="M32 8c-4.4 0-8 3.6-8 8 0-.5-3.6-4-8-4-5.5 0-10 4.5-10 10 0 4.8 3.4 8.8 8 9.8V48h36V31.8c4.6-1 8-5 8-9.8 0-5.5-4.5-10-10-10-4.4 0-7.5 3.5-8 4 0-4.4-3.6-8-8-8z" fill="white" opacity=".95"/>
                    <rect x="16" y="48" width="32" height="8" rx="2" fill="white"/>
                    <path d="M26 34v10M32 32v12M38 34v10" stroke="rgba(22,163,74,.4)" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="logo-text">
                <h2>KitchenLog</h2>
                <p>Sistem Manajemen Inventaris</p>
            </div>
        </div>

        @php
            $notifCount = \App\Models\Notifikasi::where('status','belum_dibaca')->count();
            $currentRoute = request()->route()->getName();
        @endphp

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ $currentRoute === 'dashboard' ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('menu.index') }}" class="nav-item {{ str_starts_with($currentRoute,'menu') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3v7"/></svg>
                Kelola Menu
            </a>
            <a href="{{ route('resep.index') }}" class="nav-item {{ str_starts_with($currentRoute,'resep') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                Kelola Resep
            </a>
            <a href="{{ route('bahan-baku.index') }}" class="nav-item {{ str_starts_with($currentRoute,'bahan-baku') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Kelola Bahan Baku
            </a>
            <a href="{{ route('stok.index') }}" class="nav-item {{ str_starts_with($currentRoute,'stok') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Stok Inventaris
            </a>
            <a href="{{ route('notifikasi.index') }}" class="nav-item {{ str_starts_with($currentRoute,'notifikasi') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifikasi
                @if($notifCount > 0)
                    <span class="nav-badge">{{ $notifCount }}</span>
                @endif
            </a>
            <a href="{{ route('laporan.index') }}" class="nav-item {{ str_starts_with($currentRoute,'laporan') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Laporan
            </a>
            <a href="{{ route('pengguna.index') }}" class="nav-item {{ str_starts_with($currentRoute,'pengguna') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Kelola Pengguna
            </a>
            <a href="{{ route('setoran.index') }}" class="nav-item {{ str_starts_with($currentRoute,'setoran') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Setoran Kasir
            </a>
        </nav>

        <div class="sidebar-bottom">
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="nav-item logout-btn">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══════ MAIN ═══════ --}}
    <div class="main-wrapper">
        <header class="topbar">
            <a href="{{ route('notifikasi.index') }}" class="topbar-bell">
                <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                @if($notifCount > 0)<span class="bell-dot"></span>@endif
            </a>
            <div class="topbar-user">
                <div class="topbar-avatar">
                    @if(Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="Avatar">
                    @else
                        {{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'U', 0, 1)) }}
                    @endif
                </div>
                <div class="topbar-user-info">
                    <h3>{{ Auth::user()->nama ?? Auth::user()->name }}</h3>
                    <p>{{ ucfirst(Auth::user()->role ?? 'User') }}</p>
                </div>
                <svg viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
        </header>

        <main class="content">
            {{-- Flash: Sukses --}}
            @if(session('success'))
                <div class="alert alert-success" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Flash: Error / Pesan gagal --}}
            @if(session('error'))
                <div class="alert alert-error" role="alert">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Flash: Peringatan --}}
            @if(session('warning'))
                <div class="alert alert-warning" role="alert">
                    <svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="alert alert-error" role="alert">
                    <svg viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <ul style="margin:0;padding-left:16px;list-style:disc;">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
