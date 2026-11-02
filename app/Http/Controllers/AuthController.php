<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    /** Redirect to the correct dashboard based on role. */
    private function redirectByRole(): \Illuminate\Http\RedirectResponse
    {
        $role = Auth::user()->role ?? 'kasir';
        if ($role === 'admin') return redirect()->route('dashboard');
        if ($role === 'dapur') return redirect()->route('dapur.dashboard');
        return redirect()->route('kasir.dashboard');
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginField = $request->input('email');
        $password   = $request->input('password');
        $remember   = $request->boolean('remember');

        // Try by email first, then by username
        $user = User::where('email', $loginField)
                    ->orWhere('username', $loginField)
                    ->first();

        if ($user && $user->password && Hash::check($password, $user->password)) {
            Auth::login($user, $remember);
            $request->session()->regenerate();

            return $this->redirectByRole();
        }

        return back()->withErrors([
            'email' => 'Email/Username atau password salah.',
        ])->withInput($request->only('email', 'remember'));
    }

    /**
     * Show the registration form.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectByRole();
        }
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|string|email|max:150|unique:users',
            'username' => 'required|string|max:50|unique:users|alpha_dash',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => 'required|in:admin,kasir,dapur',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, dash, dan underscore.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed'=> 'Konfirmasi password tidak cocok.',
            'password.min'      => 'Password minimal 8 karakter.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
        ]);

        $user = User::create([
            'nama'     => $request->name,
            'email'    => $request->email,
            'username' => $request->username,
            'password' => $request->password, // auto-hashed via model cast
            'role'     => $request->role,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        return $this->redirectByRole();
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
