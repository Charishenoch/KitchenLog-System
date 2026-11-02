<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHarian;
use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\Notifikasi;
use App\Models\Resep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlokasiController extends Controller
{
    /**
     * Tampilkan daftar alokasi menu hari ini.
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', today()->toDateString());

        // Ambil semua menu yang aktif/tersedia untuk opsi alokasi
        $menus = Menu::where('tersedia', true)->orderBy('nama_menu')->get();

        // Ambil data alokasi harian sesuai tanggal yang dipilih
        $alokasiList = AlokasiHarian::with('menu')
            ->whereDate('tanggal', $tanggal)
            ->get();

        // ── Hitung Ketersediaan Sisa Bahan Baku Bebas ──
        $statusBahanList = BahanBaku::orderBy('nama_bahan')->get()->map(function ($bahan) use ($tanggal) {
            $stokFisik = (float) $bahan->stok;
            // Hitung bahan yang sedang di-booking oleh menu yang dialokasikan hari ini
            $stokDiReserve = (float) DB::table('alokasi_harian')
                ->join('resep', 'alokasi_harian.menu_id', '=', 'resep.menu_id')
                ->whereDate('alokasi_harian.tanggal', $tanggal)
                ->where('resep.bahan_baku_id', $bahan->id)
                ->sum(DB::raw('alokasi_harian.porsi_tersedia * resep.jumlah'));
            // Sisa stok fisik yang benar-benar bebas dialokasikan
            $sisaBebas = max(0, $stokFisik - $stokDiReserve);
            return (object) [
                'id'            => $bahan->id,
                'nama_bahan'    => $bahan->nama_bahan,
                'satuan'        => $bahan->satuan,
                'stok_fisik'    => $stokFisik,
                'di_reserve'    => $stokDiReserve,
                'sisa_bebas'    => $sisaBebas,
                'batas_minimum' => (float) $bahan->batas_minimum,
            ];
        });
        $notifDapurCount = Notifikasi::where('status', 'belum_dibaca')->count();
        return view('dapur.alokasi', compact('menus', 'alokasiList', 'tanggal', 'statusBahanList', 'notifDapurCount'));
    }

    /**
     * Simpan reservasi porsi menu harian (Fase Reservasi - TIDAK memotong stok fisik).
     */
    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menu,id',
            'porsi'   => 'required|integer|min:1',
        ], [
            'menu_id.required' => 'Pilih menu yang akan dialokasikan.',
            'menu_id.exists'   => 'Menu tidak valid.',
            'porsi.required'   => 'Target porsi harus diisi.',
            'porsi.min'        => 'Target porsi minimal 1.',
        ]);

        $menuId          = (int) $request->menu_id;
        $targetPorsi     = (int) $request->porsi;
        $komposisiResep  = Resep::where('menu_id', $menuId)->with('bahanBaku')->get();

        if ($komposisiResep->isEmpty()) {
            return back()->withInput()->with('error', 'Menu ini belum memiliki resep/bahan baku yang ditentukan!');
        }

        // Validasi kebutuhan setiap bahan terhadap stok gudang dikurangi reservasi menu LAIN
        foreach ($komposisiResep as $resep) {
            $bahanId           = $resep->bahan_baku_id;
            $kebutuhanPerPorsi = $resep->jumlah;
            $totalKebutuhan    = $targetPorsi * $kebutuhanPerPorsi;

            // 1. Cek total stok fisik di gudang
            $stokFisik = BahanBaku::where('id', $bahanId)->sum('stok');

            // 2. Hitung total bahan yang sudah di-reserve hari ini oleh menu LAIN
            $stokDiReserve = DB::table('alokasi_harian')
                ->join('resep', 'alokasi_harian.menu_id', '=', 'resep.menu_id')
                ->whereDate('alokasi_harian.tanggal', now()->toDateString())
                ->where('resep.bahan_baku_id', $bahanId)
                ->where('alokasi_harian.menu_id', '!=', $menuId) // Pengecualian menu ini sendiri
                ->sum(DB::raw('alokasi_harian.porsi_tersedia * resep.jumlah'));

            // 3. Sisa stok aktual yang bebas dipakai
            $sisaStokAktual = $stokFisik - $stokDiReserve;

            if ($totalKebutuhan > $sisaStokAktual) {
                $porsiMaksimal = max(0, (int) floor($sisaStokAktual / $kebutuhanPerPorsi));
                $namaBahan     = $resep->bahanBaku->nama_bahan ?? 'Bahan Baku';

                return back()->withInput()->with('error', "Stok bahan '{$namaBahan}' tidak mencukupi! Tersedia {$sisaStokAktual} {$resep->satuan}. Menu ini hanya dapat dialokasikan maksimal {$porsiMaksimal} porsi.");
            }
        }

        // Simpan / Perbarui Reservasi Alokasi Harian (TIDAK memotong stok fisik gudang)
        AlokasiHarian::updateOrCreate(
            [
                'tanggal' => now()->toDateString(),
                'menu_id' => $menuId,
            ],
            [
                'porsi_dibuat'   => $targetPorsi,
                'porsi_tersedia' => $targetPorsi,
            ]
        );

        return back()->with('success', 'Alokasi menu berhasil diamankan!');
    }

    public function update(Request $request, $id)
    {
        $alokasi = AlokasiHarian::with('menu')->findOrFail($id);

        // Hitung berapa porsi yang sudah terjual oleh kasir
        $porsiTerjual = max(0, $alokasi->porsi_dibuat - $alokasi->porsi_tersedia);

        $request->validate([
            'porsi' => [
                'required',
                'integer',
                'min:' . max(1, $porsiTerjual),
            ],
        ], [
            'porsi.required' => 'Target porsi harus diisi.',
            'porsi.min'      => "Target porsi minimal {$porsiTerjual} karena sudah terjual {$porsiTerjual} porsi oleh kasir.",
        ]);

        $targetPorsiBaru = (int) $request->porsi;
        $menuId          = $alokasi->menu_id;
        $komposisiResep  = Resep::where('menu_id', $menuId)->with('bahanBaku')->get();

        // Hitung sisa porsi kasir yang baru
        $porsiTersediaBaru = $targetPorsiBaru - $porsiTerjual;

        // Validasi kebutuhan bahan baku untuk sisa porsi_tersedia yang baru
        foreach ($komposisiResep as $resep) {
            $bahanId           = $resep->bahan_baku_id;
            $kebutuhanPerPorsi = $resep->jumlah;
            $kebutuhanBahan    = $porsiTersediaBaru * $kebutuhanPerPorsi;

            // 1. Cek stok fisik aktual di gudang saat ini
            $stokFisik = BahanBaku::where('id', $bahanId)->sum('stok');

            // 2. Hitung stok yang sedang di-reserve oleh menu LAIN pada tanggal ini
            $stokDiReserveMenuLain = DB::table('alokasi_harian')
                ->join('resep', 'alokasi_harian.menu_id', '=', 'resep.menu_id')
                ->whereDate('alokasi_harian.tanggal', $alokasi->tanggal)
                ->where('resep.bahan_baku_id', $bahanId)
                ->where('alokasi_harian.id', '!=', $alokasi->id) // Abaikan alokasi yang sedang diedit ini
                ->sum(DB::raw('alokasi_harian.porsi_tersedia * resep.jumlah'));

            // 3. Sisa stok fisik yang benar-benar bebas
            $sisaStokAktual = $stokFisik - $stokDiReserveMenuLain;

            if ($kebutuhanBahan > $sisaStokAktual) {
                $porsiTersediaMaks = max(0, (int) floor($sisaStokAktual / $kebutuhanPerPorsi));
                $targetMaksimal    = $porsiTerjual + $porsiTersediaMaks;
                $namaBahan         = $resep->bahanBaku->nama_bahan ?? 'Bahan Baku';

                return back()->withInput()->with('error', "Stok bahan '{$namaBahan}' tidak cukup untuk penambahan porsi! Tersisa {$sisaStokAktual} {$resep->satuan} bebas di gudang. Menu ini maksimal dapat diset ke {$targetMaksimal} porsi.");
            }
        }

        // Simpan perubahan target porsi dan sesuaikan sisa porsi kasir
        $alokasi->update([
            'porsi_dibuat'   => $targetPorsiBaru,
            'porsi_tersedia' => $porsiTersediaBaru,
        ]);

        return back()->with('success', "Alokasi menu {$alokasi->menu->nama_menu} berhasil diubah menjadi {$targetPorsiBaru} porsi (Sisa kasir: {$porsiTersediaBaru} porsi).");
    }
}