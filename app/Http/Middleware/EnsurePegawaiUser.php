<?php

// Middleware untuk memastikan hanya pegawai non-admin yang mengakses dashboard kuesioner.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePegawaiUser
{
    /**
     * Pastikan user login adalah pegawai biasa, bukan admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->is_admin) {
            return redirect('/adminkuesioner');
        }

        if (! $user->pegawai || ! $user->pegawai->is_active) {
            abort(403, 'Akun Anda belum terhubung ke data pegawai aktif.');
        }

        return $next($request);
    }
}
