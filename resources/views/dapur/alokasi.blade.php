@extends('layouts.dapur')
@section('title','Alokasi Menu Harian')

@section('content')
<div class="page-hd">
    <div>
        <h1>Alokasi Menu Harian</h1>
        <p>Tentukan target porsi produksi dapur hari ini. Stok bahan baku akan diamankan (reservasi) untuk penjualan kasir.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        {{-- Tombol Cek Sisa Bahan Baku --}}
        <button class="btn btn-secondary" onclick="openModal('modalSisaBahan')">
            <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
            Cek Sisa Bahan Baku
        </button>
        {{-- Tombol Alokasi --}}
        <button class="btn btn-primary" onclick="openModal('modalAlokasi')">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Alokasikan Menu
        </button>
    </div>
</div>

{{-- Filter Tanggal --}}
<form method="GET" style="display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap">
    <label style="font-size:12px;font-weight:600;color:#374151">Tanggal:</label>
    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control" style="max-width:170px">
    <button class="btn btn-primary btn-sm">Filter</button>
    <a href="{{ route('dapur.alokasi.index') }}" class="btn btn-secondary btn-sm">Hari Ini</a>
</form>

{{-- Tabel Alokasi Harian --}}
<div class="card">
    <div class="card-head">
        <span class="card-title">Daftar Reservasi Menu ({{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('dddd, D MMMM YYYY') }})</span>
        <span style="font-size:12px;background:#dcfce7;color:#15803d;padding:4px 10px;border-radius:6px;font-weight:600">
            Fase Reservasi — Stok fisik gudang tidak langsung dipotong
        </span>
    </div>

    @if($alokasiList->isEmpty())
        <div class="empty-state">
            <svg viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/></svg>
            <h3>Belum ada alokasi menu pada tanggal ini</h3>
            <p>Klik tombol <strong>"Alokasikan Menu"</strong> untuk menentukan kuota porsi harian.</p>
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Menu</th>
                    <th>Kategori</th>
                    <th>Porsi Dibuat (Target)</th>
                    <th>Porsi Tersedia (Kasir)</th>
                    <th>Porsi Terjual</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alokasiList as $i => $alokasi)
                @php
                    $terjual = max(0, $alokasi->porsi_dibuat - $alokasi->porsi_tersedia);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td style="font-weight:600;color:#111827">{{ $alokasi->menu->nama_menu ?? '-' }}</td>
                    <td><span class="badge badge-gray">{{ $alokasi->menu->kategori ?? '-' }}</span></td>
                    <td style="font-weight:700">{{ $alokasi->porsi_dibuat }} porsi</td>
                    <td style="font-weight:700;color:{{ $alokasi->porsi_tersedia > 0 ? '#15803d' : '#dc2626' }}">
                        {{ $alokasi->porsi_tersedia }} porsi
                    </td>
                    <td>{{ $terjual }} porsi</td>
                    <td>
                        @if($alokasi->porsi_tersedia <= 0)
                            <span class="badge badge-red">Habis / Sold Out</span>
                        @elseif($alokasi->porsi_tersedia <= 3)
                            <span class="badge badge-orange">Menipis</span>
                        @else
                            <span class="badge badge-green">Tersedia</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-secondary btn-sm" 
                                onclick="editAlokasi({{ $alokasi->id }}, '{{ addslashes($alokasi->menu->nama_menu ?? '-') }}', {{ $alokasi->porsi_dibuat }}, {{ $terjual }})">
                            <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- Modal Input Alokasi --}}
<div id="modalAlokasi" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3 class="modal-title">Alokasikan Porsi Menu</h3>
            <button class="modal-close" onclick="closeModal('modalAlokasi')">×</button>
        </div>
        <form method="POST" action="{{ route('dapur.alokasi.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Pilih Menu</label>
                <select name="menu_id" class="form-control" required>
                    <option value="">-- Pilih Menu --</option>
                    @foreach($menus as $m)
                        <option value="{{ $m->id }}" {{ old('menu_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nama_menu }} (Rp {{ number_format($m->harga,0,',','.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Target Porsi Dibuat</label>
                <input type="number" name="porsi" class="form-control" min="1" placeholder="Contoh: 20" value="{{ old('porsi') }}" required>
                <p style="font-size:11px;color:#6b7280;margin-top:4px">Sistem akan memvalidasi stok fisik gudang dikurangi reservasi menu lain hari ini.</p>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAlokasi')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Reservasi</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Alokasi --}}
<div id="modalEditAlokasi" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <h3 class="modal-title">Ubah Jumlah Porsi Alokasi</h3>
            <button class="modal-close" onclick="closeModal('modalEditAlokasi')">×</button>
        </div>
        <form id="formEditAlokasi" method="POST" action="">
            @csrf
            @method('PUT')
            
            <div class="form-group">
                <label class="form-label">Menu</label>
                <input type="text" id="editNamaMenu" class="form-control" readonly style="background:#f9fafb;font-weight:600">
            </div>

            <div class="form-group">
                <label class="form-label">Porsi yang Sudah Terjual oleh Kasir</label>
                <div id="editInfoTerjual" style="font-size:13px;font-weight:700;color:#15803d;padding:9px 12px;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0">
                    0 porsi
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Target Porsi Dibuat Baru</label>
                <input type="number" name="porsi" id="editTargetPorsi" class="form-control" min="1" required>
                <p id="editMinWarning" style="font-size:11px;color:#6b7280;margin-top:4px">
                    Target baru tidak boleh lebih kecil dari porsi yang sudah terjual.
                </p>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditAlokasi')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Pop-Up Ketersediaan Sisa Bahan Baku --}}
<div id="modalSisaBahan" class="modal-overlay">
    <div class="modal-box" style="max-width:750px;width:95%;max-height:88vh;display:flex;flex-direction:column;">
        <div class="modal-head" style="margin-bottom:12px;">
            <div>
                <h3 class="modal-title">Status &amp; Ketersediaan Bahan Baku</h3>
                <p style="font-size:12px;color:#6b7280;margin-top:2px;">
                    Perhitungan stok gudang dikurangi kuota yang telah di-booking untuk penjualan hari ini.
                </p>
            </div>
            <button class="modal-close" onclick="closeModal('modalSisaBahan')">×</button>
        </div>

        {{-- Filter Pencarian Cepat Nama Bahan --}}
        <div style="margin-bottom:12px;">
            <input type="text" id="cariBahanInput" class="form-control" placeholder="Cari nama bahan..." onkeyup="filterTabelBahan()">
        </div>

        {{-- Tabel Bahan --}}
        <div style="overflow-y:auto;flex:1;border:1px solid #e5e7eb;border-radius:8px;">
            <table class="data-table" id="tabelSisaBahan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Bahan</th>
                        <th style="text-align:right">Stok Fisik Gudang</th>
                        <th style="text-align:right">Di-Reserve Hari Ini</th>
                        <th style="text-align:right">Sisa Bebas Dipakai</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statusBahanList as $idx => $b)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="font-weight:600;color:#111827">{{ $b->nama_bahan }}</td>
                        <td style="text-align:right;color:#374151">{{ $b->stok_fisik }} {{ $b->satuan }}</td>
                        <td style="text-align:right;color:#ea580c;font-weight:600">
                            {{ $b->di_reserve > 0 ? '-' . $b->di_reserve . ' ' . $b->satuan : '0 ' . $b->satuan }}
                        </td>
                        <td style="text-align:right;font-weight:700;color:{{ $b->sisa_bebas > $b->batas_minimum ? '#15803d' : ($b->sisa_bebas > 0 ? '#ea580c' : '#dc2626') }}">
                            {{ $b->sisa_bebas }} {{ $b->satuan }}
                        </td>
                        <td>
                            @if($b->sisa_bebas <= 0)
                                <span class="badge badge-red">Habis Ter-booking</span>
                            @elseif($b->sisa_bebas <= $b->batas_minimum)
                                <span class="badge badge-orange">Menipis</span>
                            @else
                                <span class="badge badge-green">Aman</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:24px;color:#9ca3af;">Tidak ada data bahan baku.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:14px;padding-top:10px;border-top:1px solid #f3f4f6;">
            <span style="font-size:11px;color:#9ca3af;">
                * <strong>Sisa Bebas Dipakai</strong> = Stok Fisik Gudang &minus; Total Porsi Belum Terjual &times; Kebutuhan Resep
            </span>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('modalSisaBahan')">Tutup</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editAlokasi(id, namaMenu, porsiDibuat, terjual) {
    document.getElementById('formEditAlokasi').action = "{{ url('dapur/alokasi') }}/" + id;
    document.getElementById('editNamaMenu').value = namaMenu;
    document.getElementById('editInfoTerjual').textContent = terjual + ' porsi sudah terjual';
    
    const inputPorsi = document.getElementById('editTargetPorsi');
    inputPorsi.value = porsiDibuat;
    inputPorsi.min = Math.max(1, terjual);
    
    const warningText = document.getElementById('editMinWarning');
    if (terjual > 0) {
        warningText.innerHTML = '<span style="color:#dc2626;font-weight:600">Minimal ' + terjual + ' porsi</span> karena sudah ada yang terjual ke pelanggan.';
    } else {
        warningText.textContent = 'Belum ada porsi yang terjual. Anda bebas menaikkan atau menurunkan target porsi.';
    }

    openModal('modalEditAlokasi');
}

function filterTabelBahan() {
    const input = document.getElementById('cariBahanInput');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#tabelSisaBahan tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
@endpush
@endsection