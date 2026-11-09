<?php
namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Limbah;
use App\Models\Notifikasi;
use App\Models\PenerimaanBahan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DapurController extends Controller
{
    /* ── Dashboard ─────────────────────────────────────────────────────────── */
    public function dashboard()
    {
        $totalBahan      = BahanBaku::count();
        $stokMenipis     = BahanBaku::whereColumn('stok', '<=', 'batas_minimum')->count();
        $penerimaanHariIni = PenerimaanBahan::whereDate('created_at', today())->count();
        $limbahHariIni   = Limbah::whereDate('created_at', today())->count();

        // Stok bahan baku (8 teratas)
        $stokBahans = BahanBaku::orderBy('nama_bahan')->take(8)->get();

        // Chart data: 7 hari terakhir, stok aman vs menipis vs rendah per hari
        $chartLabels = [];
        $chartAman   = [];
        $chartMenipis= [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->locale('id')->isoFormat('D MMM');
            // Snapshot stok saat ini (karena tidak ada history, gunakan current)
            $chartAman[]    = BahanBaku::whereColumn('stok', '>', 'batas_minimum')->count();
            $chartMenipis[] = BahanBaku::whereColumn('stok', '<=', 'batas_minimum')->count();
        }

        // Penerimaan terbaru (5)
        $penerimaanTerbaru = PenerimaanBahan::with('bahanBaku')
            ->latest()->take(5)->get();

        // Limbah terbaru (5)
        $limbahTerbaru = Limbah::with('bahanBaku')
            ->latest()->take(5)->get();

        // Notifikasi stok menipis (5)
        $notifStok = Notifikasi::with('bahanBaku')
            ->where('status', 'belum_dibaca')
            ->latest('tgl_kirim')->take(5)->get();

        $notifDapurCount = Notifikasi::where('status', 'belum_dibaca')->count();

        return view('dapur.dashboard', compact(
            'totalBahan', 'stokMenipis', 'penerimaanHariIni', 'limbahHariIni',
            'stokBahans', 'chartLabels', 'chartAman', 'chartMenipis',
            'penerimaanTerbaru', 'limbahTerbaru', 'notifStok', 'notifDapurCount'
        ));
    }

    /* ── Stok Bahan Baku ────────────────────────────────────────────────────── */
    public function stok(Request $request)
    {
        $search = $request->get('search', '');
        $filter = $request->get('filter', 'semua');

        $query = BahanBaku::with([
            // Eager-load batch penerimaan yg masih ada sisa, urut FIFO (tgl_masuk asc)
            'penerimaanBahan' => function ($q) {
                $q->where('sisa', '>', 0)->orderBy('tgl_masuk', 'asc')->orderBy('id', 'asc');
            }
        ]);
        if ($search) $query->where('nama_bahan', 'like', "%{$search}%");
        if ($filter === 'menipis') $query->whereColumn('stok', '<=', 'batas_minimum');
        elseif ($filter === 'aman')   $query->whereColumn('stok', '>', 'batas_minimum');

        $bahans = $query->orderBy('nama_bahan')->paginate(15)->withQueryString();
        $notifDapurCount = Notifikasi::where('status', 'belum_dibaca')->count();

        return view('dapur.stok', compact('bahans', 'search', 'filter', 'notifDapurCount'));
    }

    /* ── Penerimaan Bahan (index) ───────────────────────────────────────────── */
    public function penerimaan(Request $request)
    {
        $dari   = $request->dari   ?? today()->format('Y-m-d');
        $sampai = $request->sampai ?? today()->format('Y-m-d');

        $penerimaan = PenerimaanBahan::with('bahanBaku')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
            ->latest()->paginate(15)->withQueryString();

        $bahans = BahanBaku::orderBy('nama_bahan')->get();
        $notifDapurCount = Notifikasi::where('status', 'belum_dibaca')->count();

        return view('dapur.penerimaan', compact('penerimaan', 'bahans', 'dari', 'sampai', 'notifDapurCount'));
    }

    /* ── Simpan Penerimaan Bahan ────────────────────────────────────────────── */
    public function simpanPenerimaan(Request $request)
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

            // Catat penerimaan (FIFO: simpan tgl_masuk untuk tracking)
            PenerimaanBahan::create([
                'bahan_baku_id'  => $bahan->id,
                'staf_id'        => auth()->id(),
                'jumlah'         => $request->jumlah,
                'sisa'           => $request->jumlah,
                'tgl_masuk'      => $request->tgl_masuk,
                'tgl_kadaluarsa' => $request->tgl_kadaluarsa,
            ]);

            // Update stok bahan baku
            $bahan->increment('stok', $request->jumlah);

            // Hapus notifikasi menipis jika stok sudah aman
            if ($bahan->fresh()->stok > $bahan->batas_minimum) {
                Notifikasi::where('bahan_baku_id', $bahan->id)
                    ->where('status', 'belum_dibaca')->update(['status' => 'dibaca']);
            }
        });

        $bahan = BahanBaku::findOrFail($request->bahan_baku_id);
        return back()->with('success', "Penerimaan {$request->jumlah} {$bahan->satuan} {$bahan->nama_bahan} berhasil dicatat. Stok diperbarui.");
    }

    /* ── Limbah / Kerusakan (index) ─────────────────────────────────────────── */
    public function limbah(Request $request)
    {
        $dari   = $request->dari   ?? today()->format('Y-m-d');
        $sampai = $request->sampai ?? today()->format('Y-m-d');

        $limbah = Limbah::with('bahanBaku')
            ->whereBetween(DB::raw('DATE(tanggal)'), [$dari, $sampai])
            ->latest('tanggal')->paginate(15)->withQueryString();

        $bahans = BahanBaku::orderBy('nama_bahan')->get();
        $notifDapurCount = Notifikasi::where('status', 'belum_dibaca')->count();

        return view('dapur.limbah', compact('limbah', 'bahans', 'dari', 'sampai', 'notifDapurCount'));
    }

    /* ── Simpan Limbah ──────────────────────────────────────────────────────── */
    public function simpanLimbah(Request $request)
    {
        // Gabungkan alasan_lain jika pilih "Lainnya"
        if ($request->alasan === 'lain' && $request->alasan_lain) {
            $request->merge(['alasan' => $request->alasan_lain]);
        }

        $request->validate([
            'bahan_baku_id'      => 'required|exists:bahan_baku,id',
            'jumlah'             => 'required|numeric|min:0.01',
            'alasan'             => 'required|string|max:255',
            'penerimaan_bahan_id'=> 'nullable|exists:penerimaan_bahan,id',
        ], [
            'bahan_baku_id.required' => 'Pilih bahan baku.',
            'jumlah.required'        => 'Jumlah harus diisi.',
            'alasan.required'        => 'Alasan limbah harus diisi.',
        ]);


        $bahan = BahanBaku::findOrFail($request->bahan_baku_id);

        if ($request->jumlah > $bahan->stok) {
            return back()->withInput()->with('error', "Jumlah limbah ({$request->jumlah} {$bahan->satuan}) melebihi stok tersedia ({$bahan->stok} {$bahan->satuan}).");
        }

        DB::transaction(function () use ($request, $bahan) {
            // Catat limbah
            Limbah::create([
                'tanggal'       => now(),
                'bahan_baku_id' => $bahan->id,
                'jumlah'        => $request->jumlah,
                'alasan'        => $request->alasan,
                'staf_id'       => auth()->id(),
            ]);

            $sisa = $request->jumlah;

            if ($request->penerimaan_bahan_id) {
                // ── Mode: Potong dari batch SPESIFIK yang dipilih user ──────
                $batch = PenerimaanBahan::where('id', $request->penerimaan_bahan_id)
                    ->where('bahan_baku_id', $bahan->id)
                    ->first();

                if ($batch) {
                    if ($sisa <= $batch->sisa) {
                        // Jumlah limbah muat dalam satu batch ini
                        $batch->decrement('sisa', $sisa);
                        $sisa = 0;
                    } else {
                        // Jumlah limbah melebihi sisa batch ini → potong habis batch ini,
                        // sisanya limpahkan ke batch FIFO (terlama berikutnya)
                        $lebih = $sisa - $batch->sisa;
                        $batch->update(['sisa' => 0]);
                        $sisa = $lebih;

                        // Limpahkan sisa ke batch FIFO (kecuali batch yang sudah dipotong)
                        $batches = PenerimaanBahan::where('bahan_baku_id', $bahan->id)
                            ->where('id', '!=', $batch->id)
                            ->where('sisa', '>', 0)
                            ->orderBy('tgl_masuk', 'asc')
                            ->get();

                        foreach ($batches as $b) {
                            if ($sisa <= 0) break;
                            $kurang = min($b->sisa, $sisa);
                            $b->decrement('sisa', $kurang);
                            $sisa -= $kurang;
                        }
                    }
                }
            } else {
                // ── Mode: Potong FIFO (batch terlama dulu) — default ────────
                $batches = PenerimaanBahan::where('bahan_baku_id', $bahan->id)
                    ->where('sisa', '>', 0)
                    ->orderBy('tgl_masuk', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($sisa <= 0) break;
                    $kurang = min($batch->sisa, $sisa);
                    $batch->decrement('sisa', $kurang);
                    $sisa -= $kurang;
                }
            }

            // Update stok bahan baku
            $bahan->decrement('stok', $request->jumlah);

            // Cek dan buat notifikasi jika stok menipis
            $fresh = $bahan->fresh();
            if ($fresh->stok <= $fresh->batas_minimum) {
                $exists = Notifikasi::where('bahan_baku_id', $bahan->id)
                    ->where('status', 'belum_dibaca')->exists();
                if (!$exists) {
                    Notifikasi::create([
                        'bahan_baku_id' => $bahan->id,
                        'pesan'         => "Stok {$bahan->nama_bahan} menipis! Sisa {$fresh->stok} {$bahan->satuan} (min. {$bahan->batas_minimum}).",
                        'tgl_kirim'     => now(),
                        'status'        => 'belum_dibaca',
                    ]);
                }
            }
        });

        return back()->with('success', "Limbah {$request->jumlah} {$bahan->satuan} {$bahan->nama_bahan} berhasil dicatat. Stok dikurangi otomatis.");
    }

    /* ── API: Ambil batch aktif per bahan (untuk dropdown form limbah) ──────── */
    public function getBatch(int $bahanId)
    {
        $batches = PenerimaanBahan::where('bahan_baku_id', $bahanId)
            ->where('sisa', '>', 0)
            ->orderBy('tgl_masuk', 'asc')
            ->orderBy('id', 'asc')
            ->get(['id', 'tgl_masuk', 'sisa', 'tgl_kadaluarsa']);

        return response()->json($batches);
    }

    /* ── Notifikasi ─────────────────────────────────────────────────────────── */
    public function notifikasi()
    {
        $notifikasi = Notifikasi::with('bahanBaku')->latest('tgl_kirim')->paginate(20);
        $notifDapurCount = Notifikasi::where('status', 'belum_dibaca')->count();
        return view('dapur.notifikasi', compact('notifikasi', 'notifDapurCount'));
    }

    public function bacaNotif($id)
    {
        Notifikasi::findOrFail($id)->update(['status' => 'dibaca']);
        return back();
    }

    public function bacaSemuaNotif()
    {
        Notifikasi::where('status', 'belum_dibaca')->update(['status' => 'dibaca']);
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    /* ── Riwayat Aktivitas ──────────────────────────────────────────────────── */
    public function riwayat(Request $request)
    {
        $dari   = $request->dari   ?? today()->format('Y-m-d');
        $sampai = $request->sampai ?? today()->format('Y-m-d');

        $penerimaan = PenerimaanBahan::with('bahanBaku')
            ->whereBetween(DB::raw('DATE(created_at)'), [$dari, $sampai])
            ->latest()->get()
            ->map(fn($p) => [
                'type'    => 'penerimaan',
                'waktu'   => $p->created_at,
                'bahan'   => $p->bahanBaku->nama_bahan ?? '-',
                'jumlah'  => $p->jumlah,
                'satuan'  => $p->bahanBaku->satuan ?? '',
                'alasan'  => 'Diterima',
                'label'   => 'Penerimaan',
                'color'   => 'green',
            ]);

        $limbah = Limbah::with('bahanBaku')
            ->whereBetween(DB::raw('DATE(tanggal)'), [$dari, $sampai])
            ->latest('tanggal')->get()
            ->map(fn($l) => [
                'type'    => 'limbah',
                'waktu'   => $l->tanggal,
                'bahan'   => $l->bahanBaku->nama_bahan ?? '-',
                'jumlah'  => $l->jumlah,
                'satuan'  => $l->bahanBaku->satuan ?? '',
                'alasan'  => $l->alasan,
                'label'   => 'Limbah',
                'color'   => 'red',
            ]);

        $aktivitas = $penerimaan->concat($limbah)
            ->sortByDesc('waktu')->values();

        $notifDapurCount = Notifikasi::where('status', 'belum_dibaca')->count();

        return view('dapur.riwayat', compact('aktivitas', 'dari', 'sampai', 'notifDapurCount'));
    }
}
