@extends('layouts.app')
@section('title','Edit Bahan Baku')
@section('content')
<div class="page-hd">
    <div><h1>Edit Bahan Baku</h1></div>
    <a href="{{ route('bahan-baku.index') }}" class="btn btn-secondary">← Kembali</a>
</div>
<div class="card" style="max-width:680px;">
    <div class="card-head"><span class="card-title">Edit: {{ $bahanBaku->nama_bahan }}</span></div>
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
                    <label>Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" value="{{ old('tgl_masuk', $bahanBaku->tgl_masuk?->format('Y-m-d')) }}" class="form-control">
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
@endsection
