<?php
namespace App\Http\Controllers;

use App\Models\Resep;
use App\Models\Menu;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

class ResepController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with(['resep.bahanBaku']);
        if ($request->search) {
            $query->where('nama_menu', 'like', '%'.$request->search.'%');
        }
        $menus = $query->latest()->paginate(10)->withQueryString();
        return view('resep.index', compact('menus'));
    }

    public function create()
    {
        $menus  = Menu::orderBy('nama_menu')->get();
        $bahans = BahanBaku::orderBy('nama_bahan')->get();
        return view('resep.create', compact('menus', 'bahans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'menu_id'           => 'required|exists:menu,id',
            'bahan_baku_id'     => 'required|array|min:1',
            'bahan_baku_id.*'   => 'required|exists:bahan_baku,id',
            'jumlah'            => 'required|array',
            'jumlah.*'          => 'required|numeric|min:0.01',
            'satuan'            => 'required|array',
            'satuan.*'          => 'required|string',
        ]);

        // Hapus resep lama untuk menu ini, lalu simpan baru
        Resep::where('menu_id', $request->menu_id)->delete();

        foreach ($request->bahan_baku_id as $i => $bahanId) {
            Resep::create([
                'menu_id'       => $request->menu_id,
                'bahan_baku_id' => $bahanId,
                'jumlah'        => $request->jumlah[$i],
                'satuan'        => $request->satuan[$i],
            ]);
        }
        return redirect()->route('resep.index')->with('success', 'Resep berhasil disimpan.');
    }

    public function edit($menuId)
    {
        $menu   = Menu::findOrFail($menuId);
        $menus  = Menu::orderBy('nama_menu')->get();
        $bahans = BahanBaku::orderBy('nama_bahan')->get();
        $resep  = Resep::where('menu_id', $menuId)->with('bahanBaku')->get();
        return view('resep.edit', compact('menu', 'menus', 'bahans', 'resep'));
    }

    public function update(Request $request, $menuId)
    {
        $request->validate([
            'bahan_baku_id'   => 'required|array|min:1',
            'bahan_baku_id.*' => 'required|exists:bahan_baku,id',
            'jumlah'          => 'required|array',
            'jumlah.*'        => 'required|numeric|min:0.01',
            'satuan'          => 'required|array',
            'satuan.*'        => 'required|string',
        ]);

        Resep::where('menu_id', $menuId)->delete();
        foreach ($request->bahan_baku_id as $i => $bahanId) {
            Resep::create([
                'menu_id'       => $menuId,
                'bahan_baku_id' => $bahanId,
                'jumlah'        => $request->jumlah[$i],
                'satuan'        => $request->satuan[$i],
            ]);
        }
        return redirect()->route('resep.index')->with('success', 'Resep berhasil diperbarui.');
    }

    public function destroy($menuId)
    {
        Resep::where('menu_id', $menuId)->delete();
        return redirect()->route('resep.index')->with('success', 'Resep berhasil dihapus.');
    }
}
