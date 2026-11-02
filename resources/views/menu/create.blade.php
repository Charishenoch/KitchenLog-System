@extends('layouts.app')
@section('title','Tambah Menu')
@section('content')
<div class="page-hd">
    <div><h1>Tambah Menu</h1><p>Isi formulir di bawah untuk menambahkan menu baru.</p></div>
    <a href="{{ route('menu.index') }}" class="btn btn-secondary">
        <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
    </a>
</div>

<div class="card" style="max-width:640px;">
    <div class="card-head"><span class="card-title">Form Menu Baru</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('menu.store') }}">
            @csrf
            <div class="form-grid">
                <div class="form-group full">
                    <label>Nama Menu <span style="color:#ef4444">*</span></label>
                    <input type="text" name="nama_menu" value="{{ old('nama_menu') }}" class="form-control" placeholder="Contoh: Nasi Goreng Spesial" required>
                </div>
                <div class="form-group">
                    <label>Harga (Rp) <span style="color:#ef4444">*</span></label>
                    <input type="number" name="harga" value="{{ old('harga') }}" class="form-control" placeholder="25000" min="0" required>
                </div>
                <div class="form-group">
                    <label>Kategori <span style="color:#ef4444">*</span></label>
                    <input type="text" name="kategori" value="{{ old('kategori') }}" class="form-control" placeholder="Makanan / Minuman / Snack" list="kategori-list" required>
                    <datalist id="kategori-list">
                        <option value="Makanan">
                        <option value="Minuman">
                        <option value="Snack">
                        <option value="Dessert">
                    </datalist>
                </div>
                <div class="form-group full">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" placeholder="Deskripsi singkat menu...">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="tersedia" class="form-control">
                        <option value="1" {{ old('tersedia','1')==='1'?'selected':'' }}>Tersedia</option>
                        <option value="0" {{ old('tersedia')==='0'?'selected':'' }}>Tidak Tersedia</option>
                    </select>
                </div>
            </div>
            <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" style="width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Simpan Menu
                </button>
                <a href="{{ route('menu.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
