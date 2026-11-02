<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::query();
        if ($request->search) {
            $query->where('nama_menu', 'like', '%'.$request->search.'%')
                  ->orWhere('kategori', 'like', '%'.$request->search.'%');
        }
        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }
        $menu = $query->latest()->paginate(10)->withQueryString();
        $kategoris = Menu::distinct()->pluck('kategori');
        return view('menu.index', compact('menu', 'kategoris'));
    }

    public function create()
    {
        return view('menu.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga'     => 'required|numeric|min:0',
            'kategori'  => 'required|string|max:50',
            'deskripsi' => 'nullable|string',
            'tersedia'  => 'nullable|boolean',
        ]);
        Menu::create([
            'nama_menu' => $request->nama_menu,
            'harga'     => $request->harga,
            'kategori'  => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'tersedia'  => $request->boolean('tersedia', true),
        ]);
        return redirect()->route('menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        return view('menu.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'nama_menu' => 'required|string|max:100',
            'harga'     => 'required|numeric|min:0',
            'kategori'  => 'required|string|max:50',
        ]);
        $menu->update([
            'nama_menu' => $request->nama_menu,
            'harga'     => $request->harga,
            'kategori'  => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'tersedia'  => $request->boolean('tersedia', true),
        ]);
        return redirect()->route('menu.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menu.index')->with('success', 'Menu berhasil dihapus.');
    }
}
