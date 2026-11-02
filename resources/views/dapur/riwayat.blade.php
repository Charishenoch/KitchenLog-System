@extends('layouts.dapur')
@section('title','Riwayat Aktivitas')
@section('content')
<div class="page-hd">
    <div><h1>Riwayat Aktivitas</h1><p>Semua aktivitas penerimaan bahan dan pencatatan limbah yang dilakukan staf dapur.</p></div>
</div>

{{-- Filter --}}
<form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap">
    <label style="font-size:12px;font-weight:600;color:#374151">Dari:</label>
    <input type="date" name="dari" value="{{ $dari }}" class="form-control" style="max-width:160px">
    <label style="font-size:12px;font-weight:600;color:#374151">Sampai:</label>
    <input type="date" name="sampai" value="{{ $sampai }}" class="form-control" style="max-width:160px">
    <button class="btn btn-primary btn-sm">Filter</button>
    <a href="{{ route('dapur.riwayat') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

{{-- Summary --}}
<div class="stats-row" style="grid-template-columns:1fr 1fr;margin-bottom:16px">
    <div class="stat-card">
        <div class="stat-icon si-blue"><svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
        <div class="stat-info">
            <label>Total Penerimaan</label>
            <div class="stat-value">{{ $aktivitas->where('type','penerimaan')->count() }}</div>
            <div class="stat-unit">catatan di periode ini</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-red"><svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></div>
        <div class="stat-info">
            <label>Total Limbah</label>
            <div class="stat-value">{{ $aktivitas->where('type','limbah')->count() }}</div>
            <div class="stat-unit">catatan di periode ini</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <span class="card-title">Timeline Aktivitas ({{ $aktivitas->count() }} total)</span>
        <div style="display:flex;gap:10px">
            <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#6b7280"><div style="width:10px;height:10px;background:#16a34a;border-radius:2px"></div>Penerimaan</span>
            <span style="display:flex;align-items:center;gap:5px;font-size:11px;color:#6b7280"><div style="width:10px;height:10px;background:#dc2626;border-radius:2px"></div>Limbah</span>
        </div>
    </div>

    @if($aktivitas->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <h3>Tidak ada aktivitas</h3><p>Belum ada catatan penerimaan atau limbah di periode ini.</p>
        </div>
    @else
    <div style="padding:14px 18px">
        @foreach($aktivitas as $a)
        <div style="display:flex;gap:14px;margin-bottom:12px;align-items:flex-start">
            {{-- Timeline dot --}}
            <div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0">
                <div style="width:36px;height:36px;border-radius:9px;background:{{ $a['color']==='green'?'#dcfce7':'#fee2e2' }};color:{{ $a['color']==='green'?'#16a34a':'#dc2626' }};display:flex;align-items:center;justify-content:center">
                    @if($a['type']==='penerimaan')
                        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    @endif
                </div>
            </div>
            {{-- Content --}}
            <div style="flex:1;background:#f9fafb;border:1px solid #f3f4f6;border-radius:10px;padding:12px 14px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span class="badge {{ $a['color']==='green'?'badge-green':'badge-red' }}" style="font-size:10px">{{ $a['label'] }}</span>
                        <span style="font-size:13px;font-weight:600;color:#111827">{{ $a['bahan'] }}</span>
                    </div>
                    <span style="font-size:11px;color:#9ca3af">{{ \Carbon\Carbon::parse($a['waktu'])->locale('id')->isoFormat('D MMM YYYY, HH:mm') }}</span>
                </div>
                <div style="display:flex;gap:16px;font-size:12px;color:#6b7280">
                    <span>
                        <strong style="color:{{ $a['color']==='green'?'#15803d':'#dc2626' }}">
                            {{ $a['color']==='green'?'+':'-' }}{{ $a['jumlah'] }} {{ $a['satuan'] }}
                        </strong>
                    </span>
                    <span>{{ $a['alasan'] }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
