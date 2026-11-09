<?php
namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Notifikasi;
use App\Models\PenerimaanBahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    /**
     * Tampilkan daftar stok bahan baku beserta riwayat penerimaan terbaru.
     */
        public function index(Request $request)
    {
        $query = BahanBaku::with([
            // Eager-load batch aktif (sisa > 0) urut FIFO untuk ditampilkan di kolom batch
            'penerimaanBahan' => function ($q) {
                $q->where('sisa', '>', 0)->orderBy('tgl_masuk', 'asc')->orderBy('id', 'asc');
            }
        ]);

        if ($request->search) {
            $query->where('nama_bahan', 'like', '%' . $request->search . '%');
        }

        if ($request->status === 'menipis') {
            $query->whereColumn('stok', '<=', 'batas_minimum');
        } elseif ($request->status === 'aman') {
            $query->whereColumn('stok', '>', 'batas_minimum');
        } elseif ($request->status === 'kadaluarsa') {
            $query->whereDate('tgl_kadaluarsa', '<', today());
        }

        $bahans = $query->orderBy('nama_bahan')->paginate(15)->withQueryString();

        // ── Hitung Total Bahan yang Sedang Di-booking Alokasi Dapur Hari Ini ──
        $diReserveMap = DB::table('alokasi_harian')
            ->join('resep', 'alokasi_harian.menu_id', '=', 'resep.menu_id')
            ->whereDate('alokasi_harian.tanggal', today())
            ->groupBy('resep.bahan_baku_id')
            ->select('resep.bahan_baku_id', DB::raw('SUM(alokasi_harian.porsi_tersedia * resep.jumlah) as total_reserve'))
            ->pluck('total_reserve', 'bahan_baku_id');

        // Sematkan stok_di_reserve & sisa_bebas ke setiap objek bahan
        $bahans->getCollection()->transform(function ($b) use ($diReserveMap) {
            $b->stok_di_reserve = (float) ($diReserveMap[$b->id] ?? 0);
            $b->sisa_bebas      = max(0, (float) $b->stok - $b->stok_di_reserve);
            return $b;
        });

        // Riwayat penerimaan terbaru untuk panel info
        $penerimaanTerbaru = PenerimaanBahan::with(['bahanBaku', 'staf'])
            ->latest()
            ->take(5)
            ->get();

        return view('stok.index', compact('bahans', 'penerimaanTerbaru'));
    }

    /**
     * Catat penerimaan bahan baku baru dari admin.
     * Logika sama dengan DapurController@simpanPenerimaan agar konsisten.
     */
    public function terima(Request $request)
    {
        $request->validate([
            'bahan_baku_id'  => 'required|exists:bahan_baku,id',
            'jumlah'         => 'required|numeric|min:0.01',
            'tgl_masuk'      => 'required|date',
            'tgl_kadaluarsa' => 'nullable|date|after_or_equal:tgl_masuk',
        ], [
            'bahan_baku_id.required' => 'Pilih bahan baku.',
            'jumlah.required'        => 'Jumlah harus diisi.',
            'jumlah.min'             => 'Jumlah minimal 0.01.',
            'tgl_masuk.required'     => 'Tanggal masuk harus diisi.',
        ]);

        DB::transaction(function () use ($request) {
            $bahan = BahanBaku::findOrFail($request->bahan_baku_id);

            // Catat batch penerimaan (untuk tracking FIFO)
            PenerimaanBahan::create([
                'bahan_baku_id'  => $bahan->id,
                'staf_id'        => auth()->id(),
                'jumlah'         => $request->jumlah,
                'sisa'           => $request->jumlah,
                'tgl_masuk'      => $request->tgl_masuk,
                'tgl_kadaluarsa' => $request->tgl_kadaluarsa,
            ]);

            // Tambah stok
            $bahan->increment('stok', $request->jumlah);

            // Jika stok sudah aman kembali, tandai notifikasi menipis sebagai dibaca
            if ($bahan->fresh()->stok > $bahan->batas_minimum) {
                Notifikasi::where('bahan_baku_id', $bahan->id)
                    ->where('status', 'belum_dibaca')
                    ->update(['status' => 'dibaca']);
            }
        });

        $bahan = BahanBaku::findOrFail($request->bahan_baku_id);
        return redirect()->route('stok.index')
            ->with('success', "Penerimaan {$request->jumlah} {$bahan->satuan} {$bahan->nama_bahan} berhasil dicatat. Stok diperbarui.");
    }
}

