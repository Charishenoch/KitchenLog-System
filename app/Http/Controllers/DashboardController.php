<?php
namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\Notifikasi;
use App\Models\SetoranPendapatan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBahan      = BahanBaku::count();
        $bahanMenipis    = BahanBaku::whereColumn('stok', '<=', 'batas_minimum')->count();
        $totalMenu       = Menu::count();
        $totalTransaksi  = Transaksi::whereDate('tanggal', today())->count();

        // Setoran dari kasir
        $totalSetoran       = SetoranPendapatan::sum('jumlah');
        $setoranHariIni     = SetoranPendapatan::whereDate('tanggal_setor', today())->sum('jumlah');
        $setoranPending     = SetoranPendapatan::where('status','pending')->count();

        // Stok terendah (5 bahan)
        $stokTerendah = BahanBaku::orderByRaw('stok / GREATEST(batas_minimum, 0.001) ASC')->take(5)->get();

        // ── Grafik Stok Bahan Baku (10 bahan terendah) ────────────────────────
        $stokBahans = BahanBaku::orderBy('stok', 'asc')->take(10)->get();
        $labels          = $stokBahans->pluck('nama_bahan')->toArray();
        $chartAman       = $stokBahans->map(fn($b) => $b->stok > $b->batas_minimum ? (float)$b->stok : 0)->toArray();
        $chartPendapatan = $stokBahans->map(fn($b) => $b->stok <= $b->batas_minimum ? (float)$b->stok : 0)->toArray();
        // chartAman = stok di atas batas minimum (hijau), chartPendapatan = stok menipis (oranye)

        // Stok aman vs menipis saat ini
        $stokAman     = BahanBaku::whereColumn('stok', '>', 'batas_minimum')->count();
        $stokMenipisC = $bahanMenipis;

        // Kategori bahan baku berdasarkan nama_bahan
        $kategoriData = [
            'Sayur & Buah'     => BahanBaku::where(function($q){ $q->where('nama_bahan','like','%sayur%')->orWhere('nama_bahan','like','%buah%')->orWhere('nama_bahan','like','%tomat%')->orWhere('nama_bahan','like','%bawang%')->orWhere('nama_bahan','like','%wortel%')->orWhere('nama_bahan','like','%sawi%')->orWhere('nama_bahan','like','%cabai%'); })->count(),
            'Daging & Protein' => BahanBaku::where(function($q){ $q->where('nama_bahan','like','%daging%')->orWhere('nama_bahan','like','%ayam%')->orWhere('nama_bahan','like','%ikan%')->orWhere('nama_bahan','like','%telur%')->orWhere('nama_bahan','like','%udang%'); })->count(),
            'Bumbu & Rempah'   => BahanBaku::where(function($q){ $q->where('nama_bahan','like','%lada%')->orWhere('nama_bahan','like','%garam%')->orWhere('nama_bahan','like','%kunyit%')->orWhere('nama_bahan','like','%jahe%')->orWhere('nama_bahan','like','%rempah%'); })->count(),
            'Minyak & Lemak'   => BahanBaku::where(function($q){ $q->where('nama_bahan','like','%minyak%')->orWhere('nama_bahan','like','%mentega%'); })->count(),
            'Lainnya'          => 0,
        ];
        $sisanya = $totalBahan - array_sum(array_values($kategoriData));
        $kategoriData['Lainnya'] = max(0, $sisanya);

        // Aktivitas terbaru dari transaksi
        $aktivitas = Transaksi::with(['menu','kasir'])->latest('tanggal')->take(5)->get();

        // Bahan menipis untuk panel kanan
        $notifStok = BahanBaku::whereColumn('stok', '<=', 'batas_minimum')
                               ->orderByRaw('stok / GREATEST(batas_minimum,0.001) ASC')
                               ->take(5)->get();

        return view('dashboard', compact(
            'totalBahan','bahanMenipis','totalMenu','totalTransaksi',
            'totalSetoran','setoranHariIni','setoranPending',
            'stokTerendah','labels','chartAman','chartPendapatan',
            'stokAman','stokMenipisC','kategoriData','aktivitas','notifStok'
        ));
    }
}
