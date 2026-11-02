<?php
namespace App\Http\Controllers;

use App\Models\AlokasiHarian;
use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\Notifikasi;
use App\Models\Resep;
use App\Models\SetoranPendapatan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    /* ── Shift window helper ──────────────────────────────────────────────── */
    /**
     * Restoran buka jam 08:00 → reset shift jam 07:00 (1 jam sebelum buka).
     * "Hari ini" = 07:00 hari ini s.d. 06:59 hari berikutnya.
     * Jika sekarang < 07:00 → kita masih di shift "kemarin".
     */
    private function shiftWindow(): array
    {
        $jamReset = 7; // jam 7 pagi
        $now      = Carbon::now();

        if ($now->hour < $jamReset) {
            // Sebelum jam reset → masih shift kemarin
            $mulai = Carbon::yesterday()->setTime($jamReset, 0, 0);
            $akhir = Carbon::today()->setTime($jamReset, 0, 0)->subSecond();
        } else {
            // Sudah melewati jam reset → shift hari ini
            $mulai = Carbon::today()->setTime($jamReset, 0, 0);
            $akhir = Carbon::tomorrow()->setTime($jamReset, 0, 0)->subSecond();
        }

        return [$mulai, $akhir];
    }

    /* ── Dashboard / POS ─────────────────────────────────────────────────── */
    public function index(Request $request)
    {
        [$shiftMulai, $shiftAkhir] = $this->shiftWindow();

        $kategori = $request->get('kategori', 'semua');
        $search   = $request->get('search', '');

        $menuQuery = Menu::where('tersedia', true)->with('alokasiHariIni');
        if ($kategori !== 'semua') $menuQuery->where('kategori', $kategori);
        if ($search) $menuQuery->where('nama_menu', 'like', "%{$search}%");
        $menus     = $menuQuery->get();
        $kategoris = Menu::where('tersedia', true)->distinct()->pluck('kategori');

        // ── Statistik shift (bukan midnight–midnight) ──
        $totalMenu = Menu::where('tersedia', true)->count();

        $transaksiShiftQuery = Transaksi::whereBetween('tanggal', [$shiftMulai, $shiftAkhir])
                                        ->where('kasir_id', auth()->id());

        $transaksiHariIni  = (clone $transaksiShiftQuery)->count();
        $pendapatanHariIni = (clone $transaksiShiftQuery)->sum('total_harga');
        $bahanMenipis      = BahanBaku::whereColumn('stok', '<=', 'batas_minimum')->count();

        // ── Setoran dalam shift ini ──
        $setoranHariIni = SetoranPendapatan::where('kasir_id', auth()->id())
                            ->whereBetween('created_at', [$shiftMulai, $shiftAkhir])
                            ->latest('updated_at')
                            ->first();

        $sudahSetor    = $setoranHariIni !== null;
        $sudahDiterima = $setoranHariIni && $setoranHariIni->status === 'diterima';
        $masihPending  = $setoranHariIni && $setoranHariIni->status === 'pending';

        // ── Logika tampilan pendapatan ──
        // Belum setor        → total semua transaksi shift ini
        // Pending (menunggu) → snapshot jumlah setoran (terkunci, tidak nambah)
        // Diterima admin     → mulai 0 lagi: hanya transaksi SETELAH waktu diterima
        if ($sudahDiterima) {
            $pendapatanDisplay = Transaksi::where('kasir_id', auth()->id())
                ->where('tanggal', '>', $setoranHariIni->updated_at)
                ->sum('total_harga');
        } elseif ($masihPending) {
            $pendapatanDisplay = $setoranHariIni->jumlah; // terkunci
        } else {
            $pendapatanDisplay = $pendapatanHariIni;
        }

        // ── Riwayat terbaru (filter sesuai status setoran) ──
        // Pending  → hanya transaksi shift ini SEBELUM setoran dikirim
        // Diterima → hanya transaksi SETELAH diterima
        // Normal   → semua transaksi shift ini
        $riwayatQuery = Transaksi::with(['menu'])
                                  ->where('kasir_id', auth()->id());

        if ($masihPending) {
            // Transaksi shift ini, tapi hanya yang <= waktu setoran dibuat
            $riwayatQuery->whereBetween('tanggal', [$shiftMulai, $setoranHariIni->created_at]);
        } elseif ($sudahDiterima) {
            $riwayatQuery->where('tanggal', '>', $setoranHariIni->updated_at);
        } else {
            $riwayatQuery->whereBetween('tanggal', [$shiftMulai, $shiftAkhir]);
        }

        $riwayatTerbaru = $riwayatQuery->latest('tanggal')->take(5)->get();

        $notifCount = Notifikasi::where('status', 'belum_dibaca')->count();

        return view('kasir.dashboard', compact(
            'menus','kategoris','kategori','search',
            'totalMenu','transaksiHariIni','pendapatanHariIni','pendapatanDisplay','bahanMenipis',
            'riwayatTerbaru','notifCount','sudahSetor','sudahDiterima','masihPending'
        ));
    }


    /* ── Proses Transaksi ─────────────────────────────────────────────────── */
    public function proses(Request $request)
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menu,id',
            'items.*.porsi'   => 'required|integer|min:1',
        ]);

        $items    = $request->items;
        
        // ── Guard: blokir transaksi baru jika ada setoran PENDING ──
        [$shiftMulai, $shiftAkhir] = $this->shiftWindow();
        $setoranPending = SetoranPendapatan::where('kasir_id', auth()->id())
            ->whereBetween('created_at', [$shiftMulai, $shiftAkhir])
            ->where('status', 'pending')
            ->exists();
        if ($setoranPending) {
            return response()->json([
                'success' => false,
                'errors'  => ['Anda memiliki setoran yang sedang menunggu konfirmasi admin. Transaksi baru tidak dapat diproses sampai setoran dikonfirmasi.'],
            ], 403);
        }
        try {
            DB::transaction(function () use ($items) {
                $bahanPerluCekNotif = [];
                foreach ($items as $item) {
                    $menuId     = $item['menu_id'];
                    $qtyPesanan = (int) $item['porsi'];
                    $menu       = Menu::findOrFail($menuId);
                    // 1. Validasi & Lock Kuota Alokasi Harian Dapur
                    $alokasi = AlokasiHarian::where('menu_id', $menuId)
                        ->whereDate('tanggal', now()->toDateString())
                        ->lockForUpdate() // Mencegah race condition antar-kasir
                        ->first();
                    if (!$alokasi || $alokasi->porsi_tersedia < $qtyPesanan) {
                        $sisa = $alokasi ? $alokasi->porsi_tersedia : 0;
                        throw new \Exception("Porsi menu <strong>{$menu->nama_menu}</strong> tidak mencukupi atau Sold Out! Sisa tersedia: {$sisa} porsi.");
                    }
                    // 2. Eksekusi Pemotongan Stok Fisik Gudang (Metode FIFO)
                    $komposisiResep = Resep::where('menu_id', $menuId)->with('bahanBaku')->get();
                    foreach ($komposisiResep as $resep) {
                        $totalKebutuhanBahan = $resep->jumlah * $qtyPesanan;
                        $namaBahan           = $resep->bahanBaku->nama_bahan;
                        // Ambil batch bahan baku terkait yang stoknya > 0, urutkan dari batch terlama (FIFO)
                        $batchBahan = BahanBaku::where('nama_bahan', $namaBahan)
                            ->where('stok', '>', 0)
                            ->orderBy('tgl_masuk', 'asc') // Kunci FIFO (tgl_masuk snake_case)
                            ->lockForUpdate()
                            ->get();
                        $totalStokBatch = $batchBahan->sum('stok');
                        if ($totalStokBatch < $totalKebutuhanBahan) {
                            throw new \Exception("Stok fisik bahan <strong>{$namaBahan}</strong> di gudang tidak mencukupi untuk memenuhi pesanan!");
                        }
                        foreach ($batchBahan as $batch) {
                            if ($totalKebutuhanBahan <= 0) {
                                break;
                            }
                            if ($batch->stok >= $totalKebutuhanBahan) {
                                $batch->decrement('stok', $totalKebutuhanBahan);
                                $totalKebutuhanBahan = 0;
                            } else {
                                $totalKebutuhanBahan -= $batch->stok;
                                $batch->update(['stok' => 0]);
                            }
                        }
                        $bahanPerluCekNotif[$resep->bahan_baku_id] = true;
                    }
                    // 3. Kurangi Kuota Sisa Porsi Kasir
                    $alokasi->decrement('porsi_tersedia', $qtyPesanan);
                    // 4. Simpan Data Transaksi
                    Transaksi::create([
                        'tanggal'      => Carbon::now(),
                        'kasir_id'     => auth()->id(),
                        'menu_id'      => $menu->id,
                        'jumlah_porsi' => $qtyPesanan,
                        'total_harga'  => $menu->harga * $qtyPesanan,
                    ]);
                }
                // 5. Cek Ambang Batas Stok Menipis & Buat Notifikasi Otomatis
                foreach (array_keys($bahanPerluCekNotif) as $bid) {
                    $bahan = BahanBaku::find($bid);
                    if ($bahan && $bahan->stok <= $bahan->batas_minimum) {
                        $exists = Notifikasi::where('bahan_baku_id', $bahan->id)
                            ->where('status', 'belum_dibaca')
                            ->exists();
                        if (!$exists) {
                            Notifikasi::create([
                                'bahan_baku_id' => $bahan->id,
                                'pesan'         => "Stok {$bahan->nama_bahan} menipis! Sisa {$bahan->stok} {$bahan->satuan}.",
                                'tgl_kirim'     => now(),
                                'status'        => 'belum_dibaca',
                            ]);
                        }
                    }
                }
            });
            $noTrx = 'TRX-' . now()->format('Ymd') . '-' . str_pad(Transaksi::whereDate('tanggal', today())->count(), 4, '0', STR_PAD_LEFT);
            return response()->json([
                'success'      => true,
                'message'      => 'Transaksi berhasil, porsi kasir dan stok fisik gudang telah diperbarui!',
                'no_transaksi' => $noTrx,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors'  => [$e->getMessage()],
            ], 422);
        }
    }

    /* ── Setor Pendapatan ─────────────────────────────────────────────────── */
    public function setor(Request $request)
    {
        // Dukung body JSON dari fetch AJAX maupun form POST biasa
        if ($request->isJson()) {
            $request->merge($request->json()->all());
        }

        $request->validate(['keterangan' => 'nullable|string|max:255']);

        [$shiftMulai, $shiftAkhir] = $this->shiftWindow();

        // Cek setoran dalam shift ini
        $setoranTerakhir = SetoranPendapatan::where('kasir_id', auth()->id())
                            ->whereBetween('created_at', [$shiftMulai, $shiftAkhir])
                            ->latest('updated_at')
                            ->first();

        // Blokir jika masih ada setoran PENDING
        if ($setoranTerakhir && $setoranTerakhir->status === 'pending') {
            $msg = 'Setoran Anda masih menunggu konfirmasi admin.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg])
                : back()->with('error', $msg);
        }

        // Hitung jumlah dalam shift ini
        if ($setoranTerakhir && $setoranTerakhir->status === 'diterima') {
            $jumlah = Transaksi::where('kasir_id', auth()->id())
                        ->where('tanggal', '>', $setoranTerakhir->updated_at)
                        ->sum('total_harga');
        } else {
            $jumlah = Transaksi::where('kasir_id', auth()->id())
                        ->whereBetween('tanggal', [$shiftMulai, $shiftAkhir])
                        ->sum('total_harga');
        }

        if ($jumlah <= 0) {
            $msg = 'Belum ada pendapatan baru yang bisa disetor.';
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => $msg])
                : back()->with('error', $msg);
        }

        SetoranPendapatan::create([
            'kasir_id'      => auth()->id(),
            'jumlah'        => $jumlah,
            'tanggal_setor' => today(),
            'keterangan'    => $request->keterangan,
            'status'        => 'pending',
        ]);

        $msg = 'Setoran Rp '.number_format($jumlah,0,',','.').' berhasil dicatat. Menunggu konfirmasi admin.';
        return $request->wantsJson()
            ? response()->json(['success' => true, 'message' => $msg])
            : back()->with('success', $msg);
    }


    /* ── Riwayat Transaksi ────────────────────────────────────────────────── */
    public function riwayat(Request $request)
    {
        [$shiftMulai, $shiftAkhir] = $this->shiftWindow();

        $defaultDari   = $shiftMulai->format('Y-m-d');
        $defaultSampai = today()->format('Y-m-d');

        $dari   = $request->dari   ?? $defaultDari;
        $sampai = $request->sampai ?? $defaultSampai;

        // Cek status setoran shift ini
        $setoranHariIni = SetoranPendapatan::where('kasir_id', auth()->id())
                            ->whereBetween('created_at', [$shiftMulai, $shiftAkhir])
                            ->latest('updated_at')->first();

        $masihPending  = $setoranHariIni && $setoranHariIni->status === 'pending';
        $sudahDiterima = $setoranHariIni && $setoranHariIni->status === 'diterima';

        // Bangun query transaksi dengan filter sesuai status setoran
        $trxQuery = Transaksi::with(['menu'])->where('kasir_id', auth()->id());

        // Jika user mengubah filter tanggal → tampilkan sesuai filter (mode history)
        $isFilterCustom = $request->has('dari') || $request->has('sampai');

        if (!$isFilterCustom) {
            // Default: filter shift-aware
            if ($masihPending) {
                // Hanya transaksi sebelum setoran dikirim
                $trxQuery->whereBetween('tanggal', [$shiftMulai, $setoranHariIni->created_at]);
            } elseif ($sudahDiterima) {
                // Hanya transaksi setelah diterima admin
                $trxQuery->where('tanggal', '>', $setoranHariIni->updated_at);
            } else {
                // Semua transaksi shift ini
                $trxQuery->whereBetween('tanggal', [$shiftMulai, $shiftAkhir]);
            }
        } else {
            // User memilih filter tanggal sendiri → tampilkan semua (mode data historis)
            $trxQuery->whereBetween('tanggal', [$dari.' 00:00:00', $sampai.' 23:59:59']);
        }

        $transaksi       = $trxQuery->latest('tanggal')->paginate(20)->withQueryString();
        $totalPendapatan = (clone $trxQuery)->sum('total_harga');
        $totalTrx        = $transaksi->total();
        $notifCount      = Notifikasi::where('status', 'belum_dibaca')->count();

        // Setoran dalam shift ini (untuk info)
        $setoran = SetoranPendapatan::where('kasir_id', auth()->id())
                     ->whereBetween('created_at', [$shiftMulai, $shiftAkhir])
                     ->latest()->get();

        return view('kasir.riwayat', compact(
            'transaksi','dari','sampai','totalPendapatan','totalTrx',
            'notifCount','setoran','masihPending','sudahDiterima'
        ));
    }


    /* ── Stok (read-only) ─────────────────────────────────────────────────── */
    public function stok(Request $request)
    {
        $bahans = BahanBaku::when($request->search, fn($q) => $q->where('nama_bahan','like','%'.$request->search.'%'))
                            ->orderBy('nama_bahan')->paginate(20)->withQueryString();
        $notifCount = Notifikasi::where('status','belum_dibaca')->count();
        return view('kasir.stok', compact('bahans','notifCount'));
    }

    /* ── Notifikasi ───────────────────────────────────────────────────────── */
    public function notifikasi()
    {
        $notifikasi = Notifikasi::with('bahanBaku')->latest('tgl_kirim')->paginate(20);
        $notifCount = Notifikasi::where('status','belum_dibaca')->count();
        return view('kasir.notifikasi', compact('notifikasi','notifCount'));
    }

    public function bacaNotif($id)
    {
        Notifikasi::findOrFail($id)->update(['status'=>'dibaca']);
        return back();
    }
}
