@extends('layouts.dapur')
@section('title','Penerimaan Bahan')
@push('styles')
<style>
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.form-row-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
</style>
@endpush
@section('content')
<div class="page-hd">
    <div><h1>Penerimaan Bahan</h1><p>Catat bahan baku yang baru datang dan perbarui stok secara otomatis.</p></div>
    <button class="btn btn-primary" onclick="openModal('modalTambah')">
        <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Catat Penerimaan
    </button>
</div>

{{-- Filter tanggal --}}
<form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap">
    <label style="font-size:12px;font-weight:600;color:#374151">Dari:</label>
    <input type="date" name="dari" value="{{ $dari }}" class="form-control" style="max-width:160px">
    <label style="font-size:12px;font-weight:600;color:#374151">Sampai:</label>
    <input type="date" name="sampai" value="{{ $sampai }}" class="form-control" style="max-width:160px">
    <button class="btn btn-primary btn-sm">Filter</button>
    <a href="{{ route('dapur.penerimaan') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="card">
    <div class="card-head">
        <span class="card-title">Riwayat Penerimaan ({{ $penerimaan->total() }} catatan)</span>
        <span style="font-size:12px;color:#6b7280;background:#dbeafe;padding:3px 10px;border-radius:6px;color:#1d4ed8;font-weight:500">FIFO — urutan masuk bahan dipantau</span>
    </div>
    @if($penerimaan->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v4h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            <h3>Belum ada penerimaan</h3><p>Klik "Catat Penerimaan" untuk mulai.</p>
        </div>
    @else
    <table class="data-table">
        <thead><tr><th>No</th><th>Nama Bahan</th><th>Jumlah Diterima</th><th>Sisa (FIFO)</th><th>Tgl Masuk</th><th>Kadaluarsa</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($penerimaan as $i => $p)
            <tr>
                <td>{{ ($penerimaan->currentPage()-1)*$penerimaan->perPage()+$i+1 }}</td>
                <td style="font-weight:600">{{ $p->bahanBaku->nama_bahan ?? '-' }}</td>
                <td style="font-weight:700;color:#15803d">+{{ $p->jumlah }} {{ $p->bahanBaku->satuan ?? '' }}</td>
                <td style="color:#6b7280">{{ $p->sisa }} {{ $p->bahanBaku->satuan ?? '' }}</td>
                <td>{{ $p->tgl_masuk ? \Carbon\Carbon::parse($p->tgl_masuk)->format('d M Y, H:i') : '-' }}</td>
                <td>
                    @if($p->tgl_kadaluarsa)
                        @php $exp = \Carbon\Carbon::parse($p->tgl_kadaluarsa); @endphp
                        <span style="color:{{ $exp->isPast()?'#dc2626':($exp->diffInDays()<=7?'#ea580c':'#374151') }}">
                            {{ $exp->format('d M Y') }}
                            @if($exp->isPast()) <span class="badge badge-red" style="font-size:10px">Kadaluarsa</span>
                            @elseif($exp->diffInDays()<=7) <span class="badge badge-orange" style="font-size:10px">Segera Exp</span>
                            @endif
                        </span>
                    @else —
                    @endif
                </td>
                <td>
                    @if($p->sisa <= 0) <span class="badge badge-gray">Habis Terpakai</span>
                    @else <span class="badge badge-green">Tersedia</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-wrap">
        <span>{{ $penerimaan->firstItem() }}–{{ $penerimaan->lastItem() }} dari {{ $penerimaan->total() }}</span>
        {{ $penerimaan->links('pagination::simple-bootstrap-4') }}
    </div>
    @endif
</div>

{{-- Modal Tambah Penerimaan --}}
<div id="modalTambah" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <span class="modal-title">📦 Catat Penerimaan Bahan</span>
            <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
        </div>
        <form method="POST" action="{{ route('dapur.penerimaan.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Pilih Bahan Baku <span style="color:#dc2626">*</span></label>
                <select name="bahan_baku_id" class="form-control" required>
                    <option value="">— Pilih Bahan —</option>
                    @foreach($bahans as $b)
                        <option value="{{ $b->id }}" {{ old('bahan_baku_id')==$b->id?'selected':'' }}>
                            {{ $b->nama_bahan }} (Stok: {{ $b->stok }} {{ $b->satuan }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Jumlah Diterima <span style="color:#dc2626">*</span></label>
                    <input type="number" name="jumlah" step="0.01" min="0.01" class="form-control" placeholder="Contoh: 10" value="{{ old('jumlah') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Masuk <span style="color:#dc2626">*</span></label>
                    <input type="date" name="tgl_masuk" class="form-control" value="{{ old('tgl_masuk', date('Y-m-d')) }}" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Tanggal Kadaluarsa <span style="font-weight:400;color:#9ca3af">(opsional)</span></label>
                <input type="date" name="tgl_kadaluarsa" class="form-control" value="{{ old('tgl_kadaluarsa') }}">
            </div>
            <div style="display:flex;gap:10px;margin-top:18px">
                <button type="button" onclick="closeModal('modalTambah')" class="btn btn-secondary" style="flex:1">Batal</button>
                <button type="submit" class="btn btn-primary" style="flex:1">
                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Simpan Penerimaan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
@if($errors->any() || old('bahan_baku_id'))
    document.addEventListener('DOMContentLoaded', () => openModal('modalTambah'));
@endif
</script>
@endpush
