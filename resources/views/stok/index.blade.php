@extends('layouts.app')
@section('title','Stok Inventaris')
@section('content')
<div class="page-hd">
    <div><h1>Stok Inventaris</h1><p>Pantau sisa stok bahan baku secara real-time.</p></div>
    <button onclick="document.getElementById('modalTerima').classList.add('show')" class="btn btn-primary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Catat Penerimaan
    </button>
</div>

{{-- Stats --}}
@php
    $total   = $bahans->total();
    $menipis = \App\Models\BahanBaku::whereColumn('stok','<=','batas_minimum')->count();
    $aman    = \App\Models\BahanBaku::whereColumn('stok','>','batas_minimum')->count();
@endphp
<div class="stats-row stats-row-3" style="margin-bottom:18px;">
    <div class="stat-card">
        <div class="stat-icon-box si-teal"><svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
        <div class="stat-info"><label>Total Bahan</label><div class="stat-value">{{ $total }}</div><div class="stat-unit">jenis bahan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-green"><svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div class="stat-info"><label>Stok Aman</label><div class="stat-value">{{ $aman }}</div><div class="stat-unit">jenis bahan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-box si-orange"><svg viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></div>
        <div class="stat-info"><label>Stok Menipis</label><div class="stat-value">{{ $menipis }}</div><div class="stat-unit">jenis bahan</div></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;">
    {{-- Tabel stok --}}
    <div class="card">
        <div class="card-head">
            <span class="card-title">Daftar Stok Bahan Baku</span>
            <form method="GET" style="display:flex;gap:8px;">
                <input name="search" value="{{ request('search') }}" placeholder="Cari bahan..." class="form-control" style="width:180px;">
                <select name="status" class="form-control" style="width:140px;" onchange="this.form.submit()">
                    <option value="">Semua</option>
                    <option value="menipis" {{ request('status')==='menipis'?'selected':'' }}>Menipis</option>
                    <option value="aman"    {{ request('status')==='aman'?'selected':'' }}>Aman</option>
                    <option value="kadaluarsa" {{ request('status')==='kadaluarsa'?'selected':'' }}>Kedaluwarsa</option>
                </select>
                <button type="submit" class="btn btn-secondary">Cari</button>
            </form>
        </div>
        @if($bahans->isEmpty())
            <div class="empty-state"><p>Belum ada data bahan baku.</p></div>
        @else
                <table class="data-table">
            <thead>
                <tr>
                    <th>Nama Bahan</th>
                    <th style="text-align:right">Stok Fisik</th>
                    <th style="text-align:right">Di-Booking Hari Ini</th>
                    <th style="text-align:right">Sisa Bebas</th>
                    <th>Satuan</th>
                    <th>Min.</th>
                    <th>Kadaluwarsa</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bahans as $b)
                @php 
                    $menipis2 = $b->stok <= $b->batas_minimum; 
                    $exp = $b->tgl_kadaluarsa && $b->tgl_kadaluarsa->isPast(); 
                @endphp
                <tr>
                    <td style="font-weight:500;">{{ $b->nama_bahan }}</td>
                    
                    {{-- Stok Fisik --}}
                    <td style="text-align:right;font-weight:600;color:{{ $menipis2?'#dc2626':'#374151' }};">
                        {{ $b->stok }}
                    </td>

                    {{-- Di-Booking Hari Ini --}}
                    <td style="text-align:right;font-weight:600;color:#ea580c;">
                        {{ $b->stok_di_reserve > 0 ? '-' . $b->stok_di_reserve : '0' }}
                    </td>

                    {{-- Sisa Bebas Digunakan --}}
                    <td style="text-align:right;font-weight:700;color:{{ $b->sisa_bebas <= $b->batas_minimum ? '#dc2626' : '#15803d' }};">
                        {{ $b->sisa_bebas }}
                    </td>

                    <td>{{ $b->satuan }}</td>
                    <td>{{ $b->batas_minimum }}</td>
                    <td style="color:{{ $exp?'#dc2626':'#374151' }};">{{ $b->tgl_kadaluarsa ? $b->tgl_kadaluarsa->format('d/m/Y') : '-' }}</td>
                    <td>
                        @if($exp) 
                            <span class="badge badge-red">Kedaluwarsa</span>
                        @elseif($b->sisa_bebas <= 0)
                            <span class="badge badge-red">Habis Ter-booking</span>
                        @elseif($menipis2) 
                            <span class="badge badge-orange">Menipis</span>
                        @else 
                            <span class="badge badge-green">Aman</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination-wrap">
            <span>{{ $bahans->firstItem() }}–{{ $bahans->lastItem() }} dari {{ $bahans->total() }}</span>
            {{ $bahans->links('pagination::simple-bootstrap-4') }}
        </div>
        @endif
    </div>

    {{-- Penerimaan terbaru --}}
    <div class="card" style="height:fit-content;">
        <div class="card-head"><span class="card-title">Penerimaan Terbaru</span></div>
        @if($penerimaanTerbaru->isEmpty())
            <div class="empty-state" style="padding:24px;"><p>Belum ada catatan penerimaan.</p></div>
        @else
        @foreach($penerimaanTerbaru as $p)
        <div style="padding:12px 16px;border-bottom:1px solid #f3f4f6;">
            <div style="font-weight:600;font-size:13px;color:#111827;">{{ $p->bahanBaku->nama_bahan ?? '-' }}</div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px;">+{{ $p->jumlah }} {{ $p->bahanBaku->satuan ?? '' }} · {{ $p->tgl_masuk->format('d/m/Y') }}</div>
            <div style="font-size:11px;color:#9ca3af;">Oleh: {{ $p->staf->nama ?? '-' }}</div>
        </div>
        @endforeach
        @endif
    </div>
</div>

{{-- Modal Penerimaan --}}
<div class="modal-overlay" id="modalTerima" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal">
        <div class="modal-title">Catat Penerimaan Bahan</div>
        <form method="POST" action="{{ route('stok.terima') }}">
            @csrf
            <div class="form-group" style="margin-bottom:12px;">
                <label>Bahan Baku <span style="color:#ef4444">*</span></label>
                <select name="bahan_baku_id" class="form-control" required>
                    <option value="">-- Pilih Bahan --</option>
                    @foreach(\App\Models\BahanBaku::orderBy('nama_bahan')->get() as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_bahan }} (stok: {{ $b->stok }} {{ $b->satuan }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>Jumlah Diterima <span style="color:#ef4444">*</span></label>
                <input type="number" name="jumlah" class="form-control" min="0.01" step="0.01" placeholder="0" required>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>Tanggal Masuk <span style="color:#ef4444">*</span></label>
                <input type="date" name="tgl_masuk" value="{{ today()->format('Y-m-d') }}" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom:12px;">
                <label>Tanggal Kedaluwarsa</label>
                <input type="date" name="tgl_kadaluarsa" class="form-control">
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalTerima').classList.remove('show')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penerimaan</button>
            </div>
        </form>
    </div>
</div>
@endsection
