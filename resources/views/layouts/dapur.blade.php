<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staf Dapur') — KitchenLog</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',system-ui,sans-serif; background:#f0fdf4; color:#111827; font-size:14px; }
        a { text-decoration:none; color:inherit; }
        .app { display:flex; min-height:100vh; }
        .sidebar { width:230px; min-width:230px; background:#fff; border-right:1px solid #e5e7eb; display:flex; flex-direction:column; position:fixed; top:0; left:0; bottom:0; z-index:100; overflow-y:auto; }
        .sb-logo { padding:16px 14px; display:flex; align-items:center; gap:10px; border-bottom:1px solid #e5e7eb; min-height:64px; }
        .sb-icon { width:38px; height:38px; background:#16a34a; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .sb-icon svg { width:22px; height:22px; }
        .sb-text h2 { font-size:13px; font-weight:700; color:#16a34a; }
        .sb-text p { font-size:10px; color:#6b7280; margin-top:1px; }
        .sb-nav { flex:1; padding:10px 8px; display:flex; flex-direction:column; gap:2px; }
        .nav-item { display:flex; align-items:center; gap:10px; padding:9px 12px; border-radius:9px; color:#374151; font-size:13px; font-weight:500; transition:background .12s, color .12s; cursor:pointer; border:none; background:none; width:100%; text-align:left; }
        .nav-item:hover { background:#f0fdf4; color:#16a34a; }
        .nav-item.active { background:#16a34a; color:#fff; }
        .nav-item svg { width:17px; height:17px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .nav-badge { margin-left:auto; background:#ef4444; color:#fff; font-size:10px; font-weight:700; padding:2px 7px; border-radius:10px; }
        .nav-item.logout-btn { color:#ef4444; }
        .nav-item.logout-btn:hover { background:#fef2f2; }
        .sb-bottom { padding:10px 8px; border-top:1px solid #e5e7eb; }
        .main-wrapper { margin-left:230px; flex:1; display:flex; flex-direction:column; min-height:100vh; }
        .topbar { height:60px; background:#fff; border-bottom:1px solid #e5e7eb; display:flex; align-items:center; justify-content:space-between; padding:0 24px; position:sticky; top:0; z-index:50; }
        .topbar-left { display:flex; align-items:center; gap:14px; }
        .topbar-pill { display:flex; align-items:center; gap:6px; font-size:12px; color:#6b7280; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:5px 12px; }
        .topbar-pill svg { width:13px; height:13px; stroke:#9ca3af; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .topbar-right { display:flex; align-items:center; gap:14px; }
        .topbar-bell { position:relative; cursor:pointer; display:flex; align-items:center; padding:6px; border-radius:8px; transition:background .12s; }
        .topbar-bell:hover { background:#f3f4f6; }
        .topbar-bell svg { width:20px; height:20px; stroke:#6b7280; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .bell-dot { position:absolute; top:4px; right:4px; width:8px; height:8px; background:#ef4444; border-radius:50%; border:2px solid #fff; }
        .topbar-user { display:flex; align-items:center; gap:10px; }
        .topbar-avatar { width:36px; height:36px; border-radius:50%; background:#16a34a; color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; overflow:hidden; flex-shrink:0; }
        .topbar-avatar img { width:100%; height:100%; object-fit:cover; }
        .topbar-user-info h3 { font-size:13px; font-weight:600; color:#111827; }
        .topbar-user-info p { font-size:11px; color:#6b7280; }
        .content { padding:22px 24px; flex:1; }
        .page-hd { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
        .page-hd h1 { font-size:20px; font-weight:700; color:#111827; }
        .page-hd p { font-size:13px; color:#6b7280; margin-top:3px; }
        .card { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.07); overflow:hidden; }
        .card-head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid #f3f4f6; }
        .card-title { font-size:13px; font-weight:600; color:#111827; }
        .card-link { font-size:12px; color:#16a34a; font-weight:500; display:flex; align-items:center; gap:3px; }
        .card-body { padding:16px; }
        .btn { display:inline-flex; align-items:center; gap:7px; padding:9px 18px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; border:none; transition:opacity .15s, transform .12s; }
        .btn:hover:not(:disabled) { opacity:.88; transform:translateY(-1px); }
        .btn:disabled { opacity:.55; cursor:not-allowed; }
        .btn svg { width:15px; height:15px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .btn-primary { background:#16a34a; color:#fff; }
        .btn-secondary { background:#f3f4f6; color:#374151; }
        .btn-danger { background:#fee2e2; color:#dc2626; }
        .btn-sm { padding:5px 12px; font-size:12px; }
        .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; }
        .badge-green { background:#dcfce7; color:#15803d; }
        .badge-orange { background:#fff7ed; color:#ea580c; border:1px solid #fed7aa; }
        .badge-red { background:#fee2e2; color:#dc2626; }
        .badge-blue { background:#dbeafe; color:#1d4ed8; }
        .badge-gray { background:#f3f4f6; color:#6b7280; }
        .data-table { width:100%; border-collapse:collapse; }
        .data-table th { text-align:left; padding:10px 16px; font-size:11.5px; font-weight:600; color:#6b7280; background:#f9fafb; border-bottom:1px solid #e5e7eb; }
        .data-table td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f3f4f6; }
        .data-table tbody tr:last-child td { border-bottom:none; }
        .data-table tbody tr:hover { background:#fafafa; }
        .alert { padding:12px 16px; border-radius:10px; font-size:13px; margin-bottom:16px; display:flex; align-items:flex-start; gap:10px; }
        .alert svg { width:16px; height:16px; flex-shrink:0; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; margin-top:1px; }
        .alert-success { background:#dcfce7; border:1px solid #86efac; color:#15803d; }
        .alert-error { background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; }
        .alert-warning { background:#fff7ed; border:1px solid #fed7aa; color:#ea580c; }
        .stats-row { display:grid; gap:14px; margin-bottom:20px; }
        .stats-4 { grid-template-columns:repeat(4,1fr); }
        .stat-card { background:#fff; border-radius:12px; padding:18px 20px; display:flex; align-items:center; gap:14px; box-shadow:0 1px 3px rgba(0,0,0,.07); }
        .stat-icon { width:48px; height:48px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .stat-icon svg { width:23px; height:23px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .si-green { background:#dcfce7; color:#16a34a; }
        .si-orange { background:#fff7ed; color:#f97316; }
        .si-blue { background:#dbeafe; color:#2563eb; }
        .si-red { background:#fee2e2; color:#dc2626; }
        .stat-info label { font-size:11px; color:#6b7280; font-weight:500; display:block; margin-bottom:2px; }
        .stat-value { font-size:24px; font-weight:700; color:#111827; line-height:1.15; }
        .stat-unit { font-size:11px; color:#9ca3af; margin-top:2px; }
        .form-group { margin-bottom:14px; }
        .form-label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:5px; }
        .form-control { width:100%; padding:9px 12px; border:1.5px solid #d1d5db; border-radius:8px; font-size:13px; font-family:inherit; color:#111827; background:#fff; transition:border-color .15s, box-shadow .15s; }
        .form-control:focus { outline:none; border-color:#16a34a; box-shadow:0 0 0 3px rgba(22,163,74,.1); }
        select.form-control { cursor:pointer; }
        textarea.form-control { resize:vertical; min-height:80px; }
        .pagination-wrap { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-top:1px solid #f3f4f6; font-size:12px; color:#6b7280; }
        .empty-state { text-align:center; padding:48px 20px; color:#9ca3af; }
        .empty-state svg { width:48px; height:48px; stroke:#d1d5db; fill:none; stroke-width:1.5; stroke-linecap:round; stroke-linejoin:round; margin:0 auto 12px; display:block; }
        .empty-state h3 { font-size:15px; font-weight:600; color:#6b7280; margin-bottom:4px; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:300; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:16px; padding:28px; max-width:480px; width:92%; box-shadow:0 20px 60px rgba(0,0,0,.2); animation:modalIn .22s ease; }
        @keyframes modalIn { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
        .modal-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; }
        .modal-title { font-size:16px; font-weight:700; color:#111827; }
        .modal-close { width:30px; height:30px; border-radius:7px; border:none; background:#f3f4f6; color:#6b7280; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center; }
        .modal-close:hover { background:#e5e7eb; }
        #toast { position:fixed; bottom:24px; right:24px; z-index:999; display:flex; flex-direction:column; gap:8px; }
        .toast-item { padding:14px 18px; border-radius:10px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(0,0,0,.15); min-width:280px; display:flex; align-items:center; gap:10px; animation:slideIn .3s ease; }
        .toast-item svg { width:18px; height:18px; stroke:currentColor; fill:none; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; flex-shrink:0; }
        .toast-success { background:#16a34a; color:#fff; }
        .toast-error { background:#dc2626; color:#fff; }
        @keyframes slideIn { from { transform:translateX(100px); opacity:0; } to { transform:translateX(0); opacity:1; } }
        .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:5px; flex-shrink:0; }
        .dot-green { background:#16a34a; }
        .dot-orange { background:#f97316; }
        .dot-red { background:#dc2626; }
        @media (max-width:1100px) { .stats-4 { grid-template-columns:repeat(2,1fr); } }
    </style>
    @stack('styles')
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sb-logo">
            <div class="sb-icon">
                <svg viewBox="0 0 64 64" fill="none"><path d="M32 8c-4.4 0-8 3.6-8 8 0-.5-3.6-4-8-4-5.5 0-10 4.5-10 10 0 4.8 3.4 8.8 8 9.8V48h36V31.8c4.6-1 8-5 8-9.8 0-5.5-4.5-10-10-10-4.4 0-7.5 3.5-8 4 0-4.4-3.6-8-8-8z" fill="white" opacity=".95"/><rect x="16" y="48" width="32" height="8" rx="2" fill="white"/></svg>
            </div>
            <div class="sb-text"><h2>KitchenLog</h2><p>Sistem Manajemen Inventaris</p></div>
        </div>
        @php
            $notifDapurCount = \App\Models\Notifikasi::where('status','belum_dibaca')->count();
            $dr = request()->route()->getName();
        @endphp
        <nav class="sb-nav">
            <a href="{{ route('dapur.dashboard') }}" class="nav-item {{ $dr==='dapur.dashboard'?'active':'' }}">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('dapur.alokasi.index') }}" class="nav-item {{ str_starts_with($dr,'dapur.alokasi')?'active':'' }}">
                <svg viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                Alokasi Harian
            </a>
            <a href="{{ route('dapur.stok') }}" class="nav-item {{ $dr==='dapur.stok'?'active':'' }}">
                <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                Stok Bahan Baku
            </a>
            <a href="{{ route('dapur.penerimaan') }}" class="nav-item {{ str_starts_with($dr,'dapur.penerimaan')?'active':'' }}">
                <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                Penerimaan Bahan
            </a>
            <a href="{{ route('dapur.limbah') }}" class="nav-item {{ str_starts_with($dr,'dapur.limbah')?'active':'' }}">
                <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                Limbah / Kerusakan
            </a>
            <a href="{{ route('dapur.notifikasi') }}" class="nav-item {{ $dr==='dapur.notifikasi'?'active':'' }}">
                <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notifikasi
                @if($notifDapurCount>0)<span class="nav-badge">{{ $notifDapurCount }}</span>@endif
            </a>
            <a href="{{ route('dapur.riwayat') }}" class="nav-item {{ $dr==='dapur.riwayat'?'active':'' }}">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Riwayat Aktivitas
            </a>
        </nav>
        <div class="sb-bottom">
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="nav-item logout-btn" style="width:100%">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>
    <div class="main-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <div class="topbar-pill">
                    <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                </div>
                <div class="topbar-pill">
                    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <span id="jamDapur">{{ \Carbon\Carbon::now()->format('H:i') }}</span> WIB
                </div>
            </div>
            <div class="topbar-right">
                <a href="{{ route('dapur.notifikasi') }}" class="topbar-bell">
                    <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    @if($notifDapurCount>0)<span class="bell-dot"></span>@endif
                </a>
                <div class="topbar-user">
                    <div class="topbar-avatar">
                        @if(Auth::user()->avatar)<img src="{{ Auth::user()->avatar }}" alt="">
                        @else{{ strtoupper(substr(Auth::user()->nama ?? Auth::user()->name ?? 'D', 0, 1)) }}@endif
                    </div>
                    <div class="topbar-user-info">
                        <h3>{{ Auth::user()->nama ?? Auth::user()->name }}</h3>
                        <p>Staf Dapur</p>
                    </div>
                </div>
            </div>
        </header>
        <main class="content">
            @if(session('success'))
                <div class="alert alert-success"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>{!! session('error') !!}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<div id="toast"></div>
<script>
(function tick(){ const e=document.getElementById('jamDapur'); if(e){const n=new Date();e.textContent=n.getHours().toString().padStart(2,'0')+':'+n.getMinutes().toString().padStart(2,'0');} setTimeout(tick,10000); })();
window.showToast=function(m,t='success'){const d=document.createElement('div');d.className='toast-item toast-'+t;d.innerHTML=t==='success'?`<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span>${m}</span>`:`<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg><span>${m}</span>`;document.getElementById('toast').appendChild(d);setTimeout(()=>d.remove(),4000);};
window.openModal=id=>document.getElementById(id).classList.add('open');
window.closeModal=id=>document.getElementById(id).classList.remove('open');
document.addEventListener('click',e=>{if(e.target.classList.contains('modal-overlay'))e.target.classList.remove('open');});
</script>
@stack('scripts')
</body>
</html>
