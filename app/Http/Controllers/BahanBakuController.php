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
        $query = BahanBaku::query();

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

        $bahan = BahanBaku::create($request->only('nama_bahan','stok','satuan','batas_minimum','harga','deskripsi','tgl_kadaluarsa','tgl_masuk'));

        // Buat notifikasi jika stok sudah menipis
        if ($bahan->stok <= $bahan->batas_minimum) {
            $this->createNotif($bahan);
        }

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil ditambahkan.');
    }

    public function edit(BahanBaku $bahanBaku)
    {
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
            'tgl_masuk'     => 'nullable|date',
        ]);

        $bahanBaku->update($request->only('nama_bahan','stok','satuan','batas_minimum','harga','deskripsi','tgl_kadaluarsa','tgl_masuk'));

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
}
