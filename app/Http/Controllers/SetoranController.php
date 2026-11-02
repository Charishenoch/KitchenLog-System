<?php
namespace App\Http\Controllers;

use App\Models\SetoranPendapatan;
use Illuminate\Http\Request;

class SetoranController extends Controller
{
    public function index()
    {
        $setoran = SetoranPendapatan::with('kasir')->latest()->paginate(20);
        $totalSetoran = SetoranPendapatan::sum('jumlah');
        $totalDiterima = SetoranPendapatan::where('status','diterima')->sum('jumlah');
        $totalPending  = SetoranPendapatan::where('status','pending')->sum('jumlah');
        return view('setoran.index', compact('setoran','totalSetoran','totalDiterima','totalPending'));
    }

    public function terima($id)
    {
        SetoranPendapatan::findOrFail($id)->update(['status' => 'diterima']);
        return back()->with('success', 'Setoran berhasil dikonfirmasi diterima.');
    }
}
