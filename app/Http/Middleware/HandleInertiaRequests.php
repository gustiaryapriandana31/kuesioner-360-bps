<?php

// Middleware Inertia untuk membagikan data auth, flash, dan CSRF ke React.

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Root template yang digunakan Inertia.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Tentukan versi asset Inertia.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Data bersama yang tersedia di semua halaman Inertia.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $pegawai = $user?->pegawai;

        return [
            ...parent::share($request),
            'csrf_token' => csrf_token(),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nip' => $user->nip,
                    'is_admin' => (bool) $user->is_admin,
                    'pegawai' => $pegawai ? [
                        'id' => $pegawai->id,
                        'nama' => $pegawai->nama,
                        'jabatan' => $pegawai->jabatan,
                        'departemen' => $pegawai->departemen,
                        'foto_url' => $pegawai->foto_url,
                    ] : null,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
        ];
    }
}
