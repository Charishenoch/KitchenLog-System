@extends('layouts.dapur')
@section('title','Limbah / Kerusakan')
@section('content')
<div class="page-hd">
    <div><h1>Limbah / Kerusakan</h1><p>Catat bahan yang basi, pecah, tumpah, atau rusak. Stok dikurangi otomatis (FIFO).</p></div>
    <button class="btn btn-danger" style="background:#dc2626;color:#fff" onclick="openModal('modalLimbah')">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        Catat Limbah
    </button>
</div>

{{-- Filter tanggal --}}
<form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap">
    <label style="font-size:12px;font-weight:600;color:#374151">Dari:</label>
    <input type="date" name="dari" value="{{ $dari }}" class="form-control" style="max-width:160px">
    <label style="font-size:12px;font-weight:600;color:#374151">Sampai:</label>
    <input type="date" name="sampai" value="{{ $sampai }}" class="form-control" style="max-width:160px">
    <button class="btn btn-primary btn-sm">Filter</button>
    <a href="{{ route('dapur.limbah') }}" class="btn btn-secondary btn-sm">Reset</a>
</form>

<div class="card">
    <div class="card-head">
        <span class="card-title">Riwayat Limbah / Kerusakan ({{ $limbah->total() }} catatan)</span>
    </div>
    @if($limbah->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            <h3>Tidak ada catatan limbah</h3><p>Bagus! Tidak ada bahan yang terbuang di periode ini.</p>
        </div>
    @else
    <table class="data-table">
        <thead>
            <tr><th>No</th><th>Waktu</th><th>Nama Bahan</th><th>Jumlah Dibuang</th><th>Alasan</th><th>Dicatat Oleh</th></tr>
        </thead>
        <tbody>
            @foreach($limbah as $i => $l)
            <tr>
                <td>{{ ($limbah->currentPage()-1)*$limbah->perPage()+$i+1 }}</td>
                <td style="font-size:12px;color:#6b7280">{{ \Carbon\Carbon::parse($l->tanggal)->format('d M Y, H:i') }}</td>
                <td style="font-weight:600">{{ $l->bahanBaku->nama_bahan ?? '-' }}</td>
                <td style="font-weight:700;color:#dc2626">−{{ $l->jumlah }} {{ $l->bahanBaku->satuan ?? '' }}</td>
                <td>
                    @php
                        $alasanColors = [
                            'basi'       => ['bg'=>'#fee2e2','color'=>'#dc2626','label'=>'Basi'],
                            'busuk'      => ['bg'=>'#fee2e2','color'=>'#dc2626','label'=>'Busuk'],
                            'kadaluarsa' => ['bg'=>'#fff7ed','color'=>'#ea580c','label'=>'Kedaluwarsa'],
                            'pecah'      => ['bg'=>'#fff7ed','color'=>'#ea580c','label'=>'Pecah'],
                            'tumpah'     => ['bg'=>'#fef9c3','color'=>'#ca8a04','label'=>'Tumpah'],
                            'rusak'      => ['bg'=>'#fee2e2','color'=>'#dc2626','label'=>'Rusak'],
                        ];
                        $lower = strtolower($l->alasan);
                        $matched = null;
                        foreach ($alasanColors as $k => $v) { if (str_contains($lower, $k)) { $matched = $v; break; } }
                    @endphp
                    @if($matched)
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $matched['bg'] }};color:{{ $matched['color'] }}">{{ $matched['label'] }}</span>
                    @else
                        <span title="{{ $l->alasan }}" style="font-size:12px;color:#374151">{{ \Illuminate\Support\Str::limit($l->alasan, 30) }}</span>
                    @endif
                </td>
                <td style="font-size:12px;color:#6b7280">{{ $l->staf->nama ?? $l->staf->name ?? 'Sistem' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination-wrap">
        <span>{{ $limbah->firstItem() }}–{{ $limbah->lastItem() }} dari {{ $limbah->total() }}</span>
        {{ $limbah->links('pagination::simple-bootstrap-4') }}
    </div>
    @endif
</div>

{{-- Modal Catat Limbah --}}
<div id="modalLimbah" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <span class="modal-title">🗑️ Catat Limbah / Kerusakan</span>
            <button class="modal-close" onclick="closeModal('modalLimbah')">×</button>
        </div>
        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:12px;color:#dc2626">
            ⚠️ Stok akan dikurangi otomatis sesuai jumlah yang diinput. Sistem menggunakan metode <strong>FIFO</strong> — batch terlama dikurangi lebih dulu.
        </div>
        <form method="POST" action="{{ route('dapur.limbah.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Pilih Bahan Baku <span style="color:#dc2626">*</span></label>
                <select name="bahan_baku_id" id="pilihBahan" class="form-control" required onchange="updateStokInfo(this)">
                    <option value="">— Pilih Bahan —</option>
                    @foreach($bahans as $b)
                        <option value="{{ $b->id }}" data-stok="{{ $b->stok }}" data-satuan="{{ $b->satuan }}" {{ old('bahan_baku_id')==$b->id?'selected':'' }}>
                            {{ $b->nama_bahan }} (Stok: {{ $b->stok }} {{ $b->satuan }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div id="stokInfo" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px;color:#15803d">
                Stok tersedia: <strong id="stokValue">0</strong> <span id="satuanValue"></span>
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah yang Dibuang <span style="color:#dc2626">*</span></label>
                <input type="number" name="jumlah" step="0.01" min="0.01" class="form-control" placeholder="Contoh: 2" value="{{ old('jumlah') }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Alasan Limbah / Kerusakan <span style="color:#dc2626">*</span></label>
                <select name="alasan" class="form-control" id="pilihAlasan" onchange="toggleAlasanLain(this)" required>
                    <option value="">— Pilih Alasan —</option>
                    <option value="Basi"         {{ old('alasan')==='Basi'?'selected':'' }}>Basi</option>
                    <option value="Busuk"        {{ old('alasan')==='Busuk'?'selected':'' }}>Busuk</option>
                    <option value="Kedaluwarsa"  {{ old('alasan')==='Kedaluwarsa'?'selected':'' }}>Kedaluwarsa</option>
                    <option value="Pecah"        {{ old('alasan')==='Pecah'?'selected':'' }}>Pecah</option>
                    <option value="Tumpah"       {{ old('alasan')==='Tumpah'?'selected':'' }}>Tumpah</option>
                    <option value="Rusak"        {{ old('alasan')==='Rusak'?'selected':'' }}>Rusak / Cacat</option>
                    <option value="lain"         {{ old('alasan_lain')?'selected':'' }}>Lainnya...</option>
                </select>
                <input type="text" id="alasanLain" name="alasan_lain" class="form-control" style="margin-top:8px;display:none" placeholder="Tuliskan alasan..." value="{{ old('alasan_lain') }}" maxlength="255">
            </div>
            <div style="display:flex;gap:10px;margin-top:18px">
                <button type="button" onclick="closeModal('modalLimbah')" class="btn btn-secondary" style="flex:1">Batal</button>
                <button type="submit" class="btn" style="flex:1;background:#dc2626;color:#fff">
                    <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    Catat Limbah
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
function updateStokInfo(sel) {
    const opt = sel.options[sel.selectedIndex];
    const stok = opt.dataset.stok;
    const satuan = opt.dataset.satuan;
    const info = document.getElementById('stokInfo');
    if (sel.value) {
        document.getElementById('stokValue').textContent = stok;
        document.getElementById('satuanValue').textContent = satuan;
        info.style.display = 'block';
    } else { info.style.display = 'none'; }
}
function toggleAlasanLain(sel) {
    const el = document.getElementById('alasanLain');
    if (sel.value === 'lain') { el.style.display='block'; el.required=true; }
    else { el.style.display='none'; el.required=false; }
}
@if($errors->any() || old('bahan_baku_id'))
    document.addEventListener('DOMContentLoaded', () => { openModal('modalLimbah'); const s=document.getElementById('pilihBahan'); if(s.value) updateStokInfo(s); const a=document.getElementById('pilihAlasan'); if(a.value==='lain') toggleAlasanLain(a); });
@endif
</script>
@endpush
