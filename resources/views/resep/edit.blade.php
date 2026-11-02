@extends('layouts.app')
@section('title','Edit Resep')
@section('content')
<div class="page-hd">
    <div><h1>Edit Resep</h1><p>Perbarui komposisi bahan untuk menu: <strong>{{ $menu->nama_menu }}</strong></p></div>
    <a href="{{ route('resep.index') }}" class="btn btn-secondary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
    </a>
</div>

<div class="card" style="max-width:760px;">
    <div class="card-head"><span class="card-title">Edit Resep: {{ $menu->nama_menu }}</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('resep.update', $menu->id) }}">
            @csrf @method('PUT')

            <div style="margin-bottom:12px;font-weight:600;font-size:13px;color:#374151;">Komposisi Bahan Baku</div>
            <div id="bahan-list">
                @if($resep->isEmpty())
                <div class="bahan-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;">
                    <select name="bahan_baku_id[]" class="form-control" required>
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($bahans as $b)<option value="{{ $b->id }}">{{ $b->nama_bahan }}</option>@endforeach
                    </select>
                    <input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="0.01" step="0.01" required>
                    <input type="text" name="satuan[]" class="form-control" placeholder="Satuan">
                    <button type="button" class="btn btn-danger btn-sm hapus-bahan" disabled>Hapus</button>
                </div>
                @else
                @foreach($resep as $r)
                <div class="bahan-row" style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;">
                    <select name="bahan_baku_id[]" class="form-control" required>
                        <option value="">-- Pilih Bahan --</option>
                        @foreach($bahans as $b)
                            <option value="{{ $b->id }}" {{ $b->id == $r->bahan_baku_id ? 'selected' : '' }}>{{ $b->nama_bahan }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="jumlah[]" value="{{ $r->jumlah }}" class="form-control" min="0.01" step="0.01" required>
                    <input type="text" name="satuan[]" value="{{ $r->satuan }}" class="form-control" placeholder="Satuan">
                    <button type="button" class="btn btn-danger btn-sm hapus-bahan">Hapus</button>
                </div>
                @endforeach
                @endif
            </div>

            <button type="button" id="tambahBahan" class="btn btn-secondary" style="margin-bottom:20px;">
                <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Bahan
            </button>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('resep.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
@push('scripts')
<script>
const bahansJson = @json($bahans->map(fn($b)=>['id'=>$b->id,'nama'=>$b->nama_bahan]));
const bahanOptions = bahansJson.map(b=>`<option value="${b.id}">${b.nama}</option>`).join('');
function buildRow(){
    const div=document.createElement('div');div.className='bahan-row';
    div.style.cssText='display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:8px;margin-bottom:8px;align-items:center;';
    div.innerHTML=`<select name="bahan_baku_id[]" class="form-control" required><option value="">-- Pilih Bahan --</option>${bahanOptions}</select><input type="number" name="jumlah[]" class="form-control" placeholder="Jumlah" min="0.01" step="0.01" required><input type="text" name="satuan[]" class="form-control" placeholder="Satuan"><button type="button" class="btn btn-danger btn-sm hapus-bahan">Hapus</button>`;
    div.querySelector('.hapus-bahan').addEventListener('click',()=>{div.remove();updateHapus();});
    return div;
}
function updateHapus(){const r=document.querySelectorAll('.bahan-row');r.forEach(x=>x.querySelector('.hapus-bahan').disabled=r.length===1);}
document.getElementById('tambahBahan').addEventListener('click',()=>{document.getElementById('bahan-list').appendChild(buildRow());updateHapus();});
document.querySelectorAll('.hapus-bahan').forEach(b=>b.addEventListener('click',function(){this.closest('.bahan-row').remove();updateHapus();}));
updateHapus();
</script>
@endpush
