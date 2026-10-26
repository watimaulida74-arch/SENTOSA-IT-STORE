<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Daftar role yang diizinkan (misal: 'admin', 'staf', 'user')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Pastikan pengguna sudah login
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu untuk mengakses sistem.');
        }

        $user = Auth::user();

        // 2. Jika tidak ada role spesifik yang dibatasi, lanjutkan
        if (empty($roles)) {
            return $next($request);
        }

        // 3. Periksa apakah role user ada dalam daftar role yang diizinkan
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // 4. Jika role tidak sesuai, berikan respon 403 Forbidden dengan pesan jelas
        abort(403, 'Akses Ditolak! Anda masuk sebagai [' . strtoupper($user->role) . '] dan tidak memiliki hak akses ke modul ini.');
    }
}
