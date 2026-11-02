@extends('layouts.app')
@section('title','Edit Menu')
@section('content')
<div class="page-hd">
    <div><h1>Edit Menu</h1><p>Perbarui informasi menu.</p></div>
    <a href="{{ route('menu.index') }}" class="btn btn-secondary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
    </a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-head"><span class="card-title">Edit: {{ $menu->nama_menu }}</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('menu.update', $menu) }}">
            @csrf @method('PUT')
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nama Menu <span style="color:#ef4444">*</span></label>
                    <input type="text" name="nama_menu" value="{{ old('nama_menu', $menu->nama_menu) }}" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Harga (Rp) <span style="color:#ef4444">*</span></label>
                    <input type="number" name="harga" value="{{ old('harga', $menu->harga) }}" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label>Kategori <span style="color:#ef4444">*</span></label>
                    <input type="text" name="kategori" value="{{ old('kategori', $menu->kategori) }}" class="form-control" list="kategori-list" required>
                    <datalist id="kategori-list">
                        <option value="Makanan"><option value="Minuman"><option value="Snack"><option value="Dessert">
                    </datalist>
                </div>
                <div class="form-group full">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="tersedia" class="form-control">
                        <option value="1" {{ old('tersedia', $menu->tersedia ? '1' : '0')==='1'?'selected':'' }}>Tersedia</option>
                        <option value="0" {{ old('tersedia', $menu->tersedia ? '1' : '0')==='0'?'selected':'' }}>Tidak Tersedia</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('menu.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
