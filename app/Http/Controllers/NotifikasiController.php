<?php
namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $query = Notifikasi::with('bahanBaku');
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $notifikasi = $query->latest('tgl_kirim')->paginate(20)->withQueryString();
        $unread = Notifikasi::where('status', 'belum_dibaca')->count();
        return view('notifikasi.index', compact('notifikasi', 'unread'));
    }

    public function baca($id)
    {
        Notifikasi::findOrFail($id)->update(['status' => 'dibaca']);
        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function bacaSemua()
    {
        Notifikasi::where('status', 'belum_dibaca')->update(['status' => 'dibaca']);
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function destroy($id)
    {
        Notifikasi::findOrFail($id)->delete();
        return back()->with('success', 'Notifikasi dihapus.');
    }
}
