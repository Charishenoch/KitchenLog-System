@extends('layouts.app')
@section('title','Edit Bahan Baku')
@push('styles')
<style>
.batch-tbl { width:100%; border-collapse:collapse; font-size:12px; }
.batch-tbl th { background:#f9fafb; color:#6b7280; padding:6px 10px; font-weight:600; text-align:left; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
.batch-tbl td { padding:7px 10px; border-bottom:1px solid #f3f4f6; color:#374151; vertical-align:middle; }
.batch-tbl tr:last-child td { border-bottom:none; }
.fifo-badge { display:inline-block; background:#dbeafe; color:#1d4ed8; font-size:9px; font-weight:700; padding:1px 5px; border-radius:3px; margin-left:4px; }
#batchFormPanel { display:none; margin-top:16px; border-top:2px dashed #e5e7eb; padding-top:16px; }
</style>
@endpush
@section('content')
<div class="page-hd">
    <div><h1>Edit Bahan Baku</h1><p>Ubah data master & kelola batch masuk (FIFO).</p></div>
    <a href="{{ route('bahan-baku.index') }}" class="btn btn-secondary">← Kembali</a>
</div>

{{-- ── Form Data Master ────────────────────────────────────────────────── --}}
<div class="card" style="max-width:720px;">
    <div class="card-head"><span class="card-title">📦 Data Master: {{ $bahanBaku->nama_bahan }}</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('bahan-baku.update', $bahanBaku) }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nama Bahan <span style="color:#ef4444">*</span></label>
                    <input type="text" name="nama_bahan" value="{{ old('nama_bahan', $bahanBaku->nama_bahan) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Stok <span style="color:#ef4444">*</span></label>
                    <input type="number" name="stok" value="{{ old('stok', $bahanBaku->stok) }}" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Satuan <span style="color:#ef4444">*</span></label>
                    <input type="text" name="satuan" value="{{ old('satuan', $bahanBaku->satuan) }}" class="form-control" list="satuan-list" required>
                    <datalist id="satuan-list"><option value="kg"><option value="gram"><option value="liter"><option value="ml"><option value="pcs"><option value="ikat"><option value="butir"></datalist>
                </div>
                <div class="form-group">
                    <label>Batas Minimum Stok <span style="color:#ef4444">*</span></label>
                    <input type="number" name="batas_minimum" value="{{ old('batas_minimum', $bahanBaku->batas_minimum) }}" class="form-control" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Harga per Satuan (Rp)</label>
                    <input type="number" name="harga" value="{{ old('harga', $bahanBaku->harga) }}" class="form-control" min="0">
                </div>
                <div class="form-group">
                    <label>Tanggal Kedaluwarsa</label>
                    <input type="date" name="tgl_kadaluarsa" value="{{ old('tgl_kadaluarsa', $bahanBaku->tgl_kadaluarsa?->format('Y-m-d')) }}" class="form-control">
                </div>
                <div class="form-group full">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control">{{ old('deskripsi', $bahanBaku->deskripsi) }}</textarea>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('bahan-baku.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- ── Kelola Batch ────────────────────────────────────────────────────── --}}
<div class="card" style="max-width:720px;margin-top:20px;">
    <div class="card-head">
        <span class="card-title">📋 Kelola Batch Masuk (FIFO) — {{ $bahanBaku->nama_bahan }}</span>
    </div>

    @if($bahanBaku->penerimaanBahan->isEmpty())
        <div style="padding:20px;text-align:center;color:#9ca3af;font-size:13px">
            Belum ada batch penerimaan untuk bahan ini.
        </div>
    @else

    {{-- Tabel info batch (read-only) --}}
    <table class="batch-tbl">
        <thead>
            <tr>
                <th>#</th>
                <th>Tgl Masuk</th>
                <th>Jumlah Diterima</th>
                <th>Sisa Sekarang</th>
                <th>Tgl Kadaluarsa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bahanBaku->penerimaanBahan as $idx => $batch)
            @php
                $bExp  = $batch->tgl_kadaluarsa && \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->isPast();
                $bNear = $batch->tgl_kadaluarsa && !$bExp && \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->diffInDays(today()) <= 7;
            @endphp
            <tr id="row-batch-{{ $batch->id }}" style="cursor:pointer"
                onclick="selectBatch(
                    {{ $batch->id }},
                    '{{ \Carbon\Carbon::parse($batch->tgl_masuk)->format('Y-m-d') }}',
                    {{ $batch->jumlah }},
                    {{ $batch->sisa }},
                    '{{ $batch->tgl_kadaluarsa ? \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->format('Y-m-d') : '' }}'
                )"
                title="Klik untuk kelola batch ini">
                <td>{{ $idx+1 }}@if($idx===0)<span class="fifo-badge">NEXT</span>@endif</td>
                <td style="font-weight:600;white-space:nowrap">{{ \Carbon\Carbon::parse($batch->tgl_masuk)->format('d/m/Y') }}</td>
                <td>{{ $batch->jumlah }} {{ $bahanBaku->satuan }}</td>
                <td style="font-weight:700;color:{{ $batch->sisa > 0 ? '#15803d' : '#9ca3af' }}">
                    {{ $batch->sisa }} {{ $bahanBaku->satuan }}
                    @if($batch->sisa == 0)<span style="font-size:10px;color:#9ca3af"> (habis)</span>@endif
                </td>
                <td style="white-space:nowrap;color:{{ $bExp?'#dc2626':($bNear?'#ea580c':'#374151') }}">
                    @if($batch->tgl_kadaluarsa)
                        {{ \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->format('d/m/Y') }}
                        @if($bExp)<span style="font-size:10px"> ⚠ Exp</span>
                        @elseif($bNear)<span style="font-size:10px"> ⚠ Segera</span>
                        @endif
                    @else —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Form Kelola Batch — muncul setelah pilih dari dropdown atau klik baris --}}
    <div style="padding:16px;">

        {{-- Dropdown Pilih Batch --}}
        <div class="form-group">
            <label style="font-size:12px;font-weight:600;color:#374151">Pilih Batch yang Ingin Dikelola</label>
            <select id="dropdownBatch" class="form-control" onchange="onDropdownChange(this)">
                <option value="">— Pilih batch —</option>
                @foreach($bahanBaku->penerimaanBahan as $idx => $batch)
                <option value="{{ $batch->id }}"
                    data-tgl="{{ \Carbon\Carbon::parse($batch->tgl_masuk)->format('Y-m-d') }}"
                    data-jumlah="{{ $batch->jumlah }}"
                    data-sisa="{{ $batch->sisa }}"
                    data-kadal="{{ $batch->tgl_kadaluarsa ? \Carbon\Carbon::parse($batch->tgl_kadaluarsa)->format('Y-m-d') : '' }}">
                    #{{ $idx+1 }} — {{ \Carbon\Carbon::parse($batch->tgl_masuk)->format('d/m/Y') }}
                    (Sisa: {{ $batch->sisa }} {{ $bahanBaku->satuan }})
                    {{ $idx === 0 ? '★ NEXT' : '' }}
                </option>
                @endforeach
            </select>
            <span style="font-size:11px;color:#9ca3af">atau klik langsung baris batch di atas</span>
        </div>

        {{-- Form Edit + Hapus (muncul setelah pilih batch) --}}
        <div id="batchFormPanel">
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#1d4ed8">
                ✏️ Mengedit batch: <strong id="labelBatchDipilih"></strong>
            </div>

            <form id="formEditBatch" method="POST">
                @csrf @method('PUT')
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Tanggal Masuk <span style="color:#dc2626">*</span></label>
                        <input type="date" name="tgl_masuk" id="ebTglMasuk" class="form-control" required>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Tanggal Kadaluarsa</label>
                        <input type="date" name="tgl_kadaluarsa" id="ebTglKadal" class="form-control">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Jumlah Diterima <span style="color:#dc2626">*</span></label>
                        <input type="number" name="jumlah" id="ebJumlah" class="form-control" step="0.01" min="0" required>
                        <span style="font-size:10px;color:#9ca3af">{{ $bahanBaku->satuan }}</span>
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Sisa Sekarang <span style="color:#dc2626">*</span></label>
                        <input type="number" name="sisa" id="ebSisa" class="form-control" step="0.01" min="0" required>
                        <span style="font-size:10px;color:#9ca3af">{{ $bahanBaku->satuan }} — ubah sisa akan sesuaikan stok master</span>
                    </div>
                </div>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    <button type="button" id="btnHapusBatch" class="btn btn-danger" onclick="hapusBatch()">🗑 Hapus Batch Ini</button>
                    <button type="button" class="btn btn-secondary" onclick="resetPilihan()">✕ Batal</button>
                </div>
            </form>

            {{-- Hidden form untuk hapus (DELETE method) --}}
            <form id="formHapusBatch" method="POST" style="display:none">
                @csrf @method('DELETE')
            </form>
        </div>

        <div style="margin-top:12px;font-size:11px;color:#9ca3af">
            ⚠️ Mengubah <strong>Sisa</strong> batch akan otomatis menyesuaikan total stok bahan baku.
        </div>
    </div>
    @endif
</div>
@endsection
@php
$batchDataForJs = $bahanBaku->penerimaanBahan->map(function($b) use ($bahanBaku) {
    return [
        'id'             => $b->id,
        'tgl_masuk'      => \Carbon\Carbon::parse($b->tgl_masuk)->format('Y-m-d'),
        'jumlah'         => $b->jumlah,
        'sisa'           => $b->sisa,
        'tgl_kadaluarsa' => $b->tgl_kadaluarsa ? \Carbon\Carbon::parse($b->tgl_kadaluarsa)->format('Y-m-d') : '',
        'label'          => \Carbon\Carbon::parse($b->tgl_masuk)->format('d/m/Y') . ' (Sisa: ' . $b->sisa . ' ' . $bahanBaku->satuan . ')',
    ];
});
@endphp
@push('scripts')
<script>
// Data batch dari server
const batchData = @json($batchDataForJs);

let selectedBatchId = null;

function fillForm(batch) {
    selectedBatchId = batch.id;
    document.getElementById('ebTglMasuk').value = batch.tgl_masuk;
    document.getElementById('ebTglKadal').value  = batch.tgl_kadaluarsa;
    document.getElementById('ebJumlah').value    = batch.jumlah;
    document.getElementById('ebSisa').value      = batch.sisa;
    document.getElementById('labelBatchDipilih').textContent = batch.label;
    document.getElementById('formEditBatch').action = `/bahan-baku/batch/${batch.id}`;
    document.getElementById('formHapusBatch').action = `/bahan-baku/batch/${batch.id}`;
    document.getElementById('batchFormPanel').style.display = 'block';

    // Highlight baris yang dipilih
    document.querySelectorAll('.batch-tbl tbody tr').forEach(r => r.style.background = '');
    const row = document.getElementById('row-batch-' + batch.id);
    if (row) row.style.background = '#eff6ff';
}

// Klik baris tabel → isi form
function selectBatch(id, tgl, jumlah, sisa, kadal) {
    const batch = { id, tgl_masuk: tgl, jumlah, sisa, tgl_kadaluarsa: kadal,
        label: document.querySelector(`#row-batch-${id} td:nth-child(2)`).textContent.trim()
            + ` (Sisa: ${sisa} {{ $bahanBaku->satuan }})` };
    fillForm(batch);
    // Sync dropdown
    document.getElementById('dropdownBatch').value = id;
}

// Dropdown berubah
function onDropdownChange(sel) {
    if (!sel.value) { resetPilihan(); return; }
    const batch = batchData.find(b => b.id == sel.value);
    if (batch) fillForm(batch);
}

// Hapus batch
function hapusBatch() {
    if (!selectedBatchId) return;
    const batch = batchData.find(b => b.id == selectedBatchId);
    if (!confirm(`Hapus batch "${batch?.label}"?\nStok master akan dikurangi sebesar sisa batch.`)) return;
    document.getElementById('formHapusBatch').submit();
}

// Reset
function resetPilihan() {
    selectedBatchId = null;
    document.getElementById('dropdownBatch').value = '';
    document.getElementById('batchFormPanel').style.display = 'none';
    document.querySelectorAll('.batch-tbl tbody tr').forEach(r => r.style.background = '');
}
</script>
@endpush
