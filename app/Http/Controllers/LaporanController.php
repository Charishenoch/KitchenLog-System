<?php
namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\Limbah;
use App\Models\BahanBaku;
use App\Models\PenerimaanBahan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $dari  = $request->dari  ? Carbon::parse($request->dari)  : Carbon::now()->startOfMonth();
        $sampai= $request->sampai? Carbon::parse($request->sampai)->endOfDay() : Carbon::now()->endOfDay();
        $jenis = $request->jenis ?? 'stok';

        // Laporan stok saat ini
        $laporanStok = BahanBaku::orderBy('nama_bahan')->get();

        // Laporan transaksi
        $laporanTransaksi = Transaksi::with(['menu','kasir'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->latest('tanggal')
            ->get();
        $totalPendapatan = $laporanTransaksi->sum('total_harga');

        // Laporan limbah
        $laporanLimbah = Limbah::with(['bahanBaku','staf'])
            ->whereBetween('tanggal', [$dari, $sampai])
            ->latest('tanggal')
            ->get();

        // Laporan penerimaan
        $laporanPenerimaan = PenerimaanBahan::with(['bahanBaku','staf'])
            ->whereBetween('tgl_masuk', [$dari->toDateString(), $sampai->toDateString()])
            ->latest('tgl_masuk')
            ->get();

        // Grafik transaksi harian (untuk chart)
        $chartLabels  = [];
        $chartData    = [];
        for ($d = $dari->copy(); $d->lte($sampai) && count($chartLabels) <= 30; $d->addDay()) {
            $chartLabels[] = $d->format('d/m');
            $chartData[]   = $laporanTransaksi->filter(fn($t) => Carbon::parse($t->tanggal)->isSameDay($d))->sum('total_harga');
        }

        return view('laporan.index', compact(
            'dari','sampai','jenis',
            'laporanStok','laporanTransaksi','totalPendapatan',
            'laporanLimbah','laporanPenerimaan',
            'chartLabels','chartData'
        ));
    }
}
