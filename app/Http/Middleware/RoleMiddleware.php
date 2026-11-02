<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RoleMiddleware
 *
 * Membatasi akses route berdasarkan role pengguna.
 * Penggunaan di route: ->middleware('role:admin') atau ->middleware('role:admin,kasir')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Jika belum login, arahkan ke halaman login
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role ?? 'kasir';

        // Jika role cocok, lanjutkan request
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // Jika tidak cocok, arahkan ke dashboard sesuai role masing-masing
        return match ($userRole) {
            'admin'  => redirect()->route('dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.'),
            'dapur'  => redirect()->route('dapur.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.'),
            default  => redirect()->route('kasir.dashboard')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.'),
        };
    }
}
