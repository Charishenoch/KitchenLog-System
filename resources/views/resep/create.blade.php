@extends('layouts.app')
@section('title','Buat Resep')
@section('content')
<div class="page-hd">
    <div><h1>Buat Resep</h1><p>Tentukan komposisi bahan baku untuk setiap menu.</p></div>
    <a href="{{ route('resep.index') }}" class="btn btn-secondary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
    </a>
</div>

<div class="card" style="max-width:760px;">
    <div class="card-head"><span class="card-title">Form Resep</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('resep.store') }}" id="resepForm">
            @csrf
            <div class="form-group" style="margin-bottom:20px;">
                <label>Pilih Menu <span style="color:#ef4444">*</span></label>
                <select name="menu_id" class="form-control" required>
                    <option value="">-- Pilih Menu --</option>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}" {{ old('menu_id')==$m->id?'selected':'' }}>{{ $m->nama_menu }}</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom:12px;font-weight:600;font-size:13px;color:#374151;">Komposisi Bahan Baku</div>
            <div id="bahan-list">
                <div class="bahan-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;">
                    <select name="bahan_baku_id[]" class="form-control" required>
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($bahans as $b)
                            <option value="{{ $b->id }}">{{ $b->nama_bahan }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="0.01" step="0.01" required>
                    <input type="text" name="satuan[]" class="form-control" placeholder="Satuan (kg/ltr/pcs)">
                    <button type="button" class="btn btn-danger btn-sm hapus-bahan" style="white-space:nowrap;" disabled>Hapus</button>
                </div>
            </div>

            <button type="button" id="tambahBahan" class="btn btn-secondary" style="margin-bottom:20px;">
                <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Bahan
            </button>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Resep</button>
                <a href="{{ route('resep.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const bahansJson = @json($bahans->map(fn($b)=>['id'=>$b->id,'nama'=>$b->nama_bahan,'satuan'=>$b->satuan]));
const bahanOptions = bahansJson.map(b=>`<option value="${b.id}">${b.nama}</option>`).join('');

function buildRow() {
    const div = document.createElement('div');
    div.className = 'bahan-row';
    div.style.cssText = 'display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;';
    div.innerHTML = `
        <select name="bahan_baku_id[]" class="form-control" required><option value="">-- Pilih Bahan --</option>${bahanOptions}</select>
        <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="0.01" step="0.01" required>
        <input type="text" name="satuan[]" class="form-control" placeholder="Satuan">
        <button type="button" class="btn btn-danger btn-sm hapus-bahan">Hapus</button>`;
    div.querySelector('.hapus-bahan').addEventListener('click', () => { div.remove(); updateHapus(); });
    return div;
}

function updateHapus() {
    const rows = document.querySelectorAll('.bahan-row');
    rows.forEach((r,i) => r.querySelector('.hapus-bahan').disabled = rows.length === 1);
}

document.getElementById('tambahBahan').addEventListener('click', () => {
    document.getElementById('bahan-list').appendChild(buildRow());
    updateHapus();
});

document.querySelector('.hapus-bahan').addEventListener('click', function() {
    this.closest('.bahan-row').remove(); updateHapus();
});
</script>
@endpush
