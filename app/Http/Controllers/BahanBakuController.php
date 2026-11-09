<?php
namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Limbah;
use App\Models\Notifikasi;
use App\Models\PenerimaanBahan;
use App\Models\Resep;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BahanBakuController extends Controller
{
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
        return view('bahan-baku.index', compact('bahans'));
    }

    public function create()
    {
        return view('bahan-baku.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan'    => 'required|string|max:100',
            'stok'          => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:20',
            'batas_minimum' => 'required|numeric|min:0',
            'harga'         => 'nullable|numeric|min:0',
            'deskripsi'     => 'nullable|string',
            'tgl_kadaluarsa'=> 'nullable|date',
            'tgl_masuk'     => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            $bahan = BahanBaku::create($request->only(
                'nama_bahan','stok','satuan','batas_minimum','harga','deskripsi','tgl_kadaluarsa'
            ));

            // Jika ada stok awal, buat batch penerimaan pertama agar FIFO langsung berjalan
            if ($bahan->stok > 0) {
                PenerimaanBahan::create([
                    'bahan_baku_id'  => $bahan->id,
                    'staf_id'        => auth()->id(),
                    'jumlah'         => $bahan->stok,
                    'sisa'           => $bahan->stok,
                    'tgl_masuk'      => $request->tgl_masuk ?? today(),
                    'tgl_kadaluarsa' => $request->tgl_kadaluarsa,
                ]);
            }

            // Buat notifikasi jika stok sudah menipis
            if ($bahan->stok <= $bahan->batas_minimum) {
                $this->createNotif($bahan);
            }
        });

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(BahanBaku $bahanBaku)
    {
        $bahanBaku->load([
            'penerimaanBahan' => fn($q) => $q->orderBy('tgl_masuk', 'asc')->orderBy('id', 'asc'),
        ]);
        return view('bahan-baku.edit', compact('bahanBaku'));
    }

    public function update(Request $request, BahanBaku $bahanBaku)
    {
        $request->validate([
            'nama_bahan'    => 'required|string|max:100',
            'stok'          => 'required|numeric|min:0',
            'satuan'        => 'required|string|max:20',
            'batas_minimum' => 'required|numeric|min:0',
            'harga'         => 'nullable|numeric|min:0',
            'deskripsi'     => 'nullable|string',
            'tgl_kadaluarsa'=> 'nullable|date',
        ]);

        // Update master (tgl_masuk TIDAK disimpan di master — tracking per batch ada di penerimaan_bahan)
        $bahanBaku->update($request->only('nama_bahan','stok','satuan','batas_minimum','harga','deskripsi','tgl_kadaluarsa'));

        // Update notifikasi
        if ($bahanBaku->stok <= $bahanBaku->batas_minimum) {
            $this->createNotif($bahanBaku);
        }

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil diperbarui.');
    }

    public function destroy(BahanBaku $bahanBaku)
    {
        $nama = $bahanBaku->nama_bahan;

        DB::transaction(function () use ($bahanBaku) {
            // Hapus relasi anak dulu (urutan penting!)
            Notifikasi::where('bahan_baku_id', $bahanBaku->id)->delete();
            Limbah::where('bahan_baku_id', $bahanBaku->id)->delete();
            PenerimaanBahan::where('bahan_baku_id', $bahanBaku->id)->delete();
            Resep::where('bahan_baku_id', $bahanBaku->id)->delete();

            // Baru hapus bahan baku utama
            $bahanBaku->delete();
        });

        return redirect()->route('bahan-baku.index')
            ->with('success', "Bahan baku \"{$nama}\" beserta resep terkait berhasil dihapus.");
    }

    private function createNotif(BahanBaku $bahan): void
    {
        // Cek jika notifikasi belum dibaca sudah ada
        $exists = Notifikasi::where('bahan_baku_id', $bahan->id)
                            ->where('status', 'belum_dibaca')
                            ->exists();
        if (!$exists) {
            Notifikasi::create([
                'bahan_baku_id' => $bahan->id,
                'pesan'         => "Stok {$bahan->nama_bahan} menipis! Sisa {$bahan->stok} {$bahan->satuan} (Min. {$bahan->batas_minimum} {$bahan->satuan}).",
                'tgl_kirim'     => Carbon::now(),
                'status'        => 'belum_dibaca',
            ]);
        }
    }

    /* ── Edit Batch Penerimaan (admin) ──────────────────────────────────────── */
    public function updateBatch(Request $request, int $batchId)
    {
        $request->validate([
            'tgl_masuk'      => 'required|date',
            'tgl_kadaluarsa' => 'nullable|date',
            'jumlah'         => 'required|numeric|min:0',
            'sisa'           => 'required|numeric|min:0',
        ]);

        $batch = PenerimaanBahan::findOrFail($batchId);
        $bahan = BahanBaku::findOrFail($batch->bahan_baku_id);

        DB::transaction(function () use ($request, $batch, $bahan) {
            $selisihJumlah = $request->jumlah - $batch->jumlah;
            $selisihSisa   = $request->sisa   - $batch->sisa;

            $batch->update([
                'tgl_masuk'      => $request->tgl_masuk,
                'tgl_kadaluarsa' => $request->tgl_kadaluarsa,
                'jumlah'         => $request->jumlah,
                'sisa'           => $request->sisa,
            ]);

            // Sinkronisasi stok master bahan baku berdasarkan selisih sisa
            if ($selisihSisa != 0) {
                $bahan->increment('stok', $selisihSisa);
            }
        });

        return redirect()->route('bahan-baku.index')
            ->with('success', "Batch {$bahan->nama_bahan} (masuk " . \Carbon\Carbon::parse($request->tgl_masuk)->format('d/m/Y') . ") berhasil diperbarui.");
    }

    /* ── Hapus Batch Penerimaan (admin) ─────────────────────────────────────── */
    public function destroyBatch(int $batchId)
    {
        $batch = PenerimaanBahan::findOrFail($batchId);
        $bahan = BahanBaku::findOrFail($batch->bahan_baku_id);
        $tgl   = \Carbon\Carbon::parse($batch->tgl_masuk)->format('d/m/Y');

        DB::transaction(function () use ($batch, $bahan) {
            // Kurangi stok master sebesar sisa batch ini
            if ($batch->sisa > 0) {
                $bahan->decrement('stok', $batch->sisa);
            }
            $batch->delete();
        });

        return redirect()->route('bahan-baku.index')
            ->with('success', "Batch {$bahan->nama_bahan} (masuk {$tgl}) berhasil dihapus.");
    }
}
