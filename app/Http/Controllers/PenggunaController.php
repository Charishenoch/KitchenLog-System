<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->search) {
            $query->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
        }
        if ($request->role) {
            $query->where('role', $request->role);
        }
        $pengguna = $query->latest()->paginate(15)->withQueryString();
        return view('pengguna.index', compact('pengguna'));
    }

    public function create()
    {
        return view('pengguna.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:admin,kasir,dapur',
        ]);

        User::create([
            'nama'     => $request->nama,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => $request->password, // auto-hashed by model cast
            'role'     => $request->role,
        ]);

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna)
    {
        return view('pengguna.edit', compact('pengguna'));
    }

    public function update(Request $request, User $pengguna)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'username' => 'nullable|string|max:50|unique:users,username,'.$pengguna->id,
            'email'    => 'required|email|unique:users,email,'.$pengguna->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role'     => 'required|in:admin,kasir,dapur',
        ]);

        $data = [
            'nama'     => $request->nama,
            'username' => $request->username,
            'email'    => $request->email,
            'role'     => $request->role,
        ];
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }
        $pengguna->update($data);

        return redirect()->route('pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        // Cegah hapus akun sendiri
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun yang sedang digunakan.');
        }

        DB::transaction(function () use ($pengguna) {
            // Nullify staf_id di tabel yang mereferensikan user ini
            // agar histori penerimaan & limbah tetap tersimpan
            DB::table('penerimaan_bahan')->where('staf_id', $pengguna->id)->update(['staf_id' => null]);
            DB::table('limbah')->where('staf_id', $pengguna->id)->update(['staf_id' => null]);
            DB::table('transaksi')->where('kasir_id', $pengguna->id)->update(['kasir_id' => null]);
            DB::table('setoran_pendapatan')->where('kasir_id', $pengguna->id)->update(['kasir_id' => null]);

            $pengguna->delete();
        });

        return redirect()->route('pengguna.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
