<?php

// Controller login khusus pegawai biasa untuk masuk ke dashboard kuesioner.

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PegawaiLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PegawaiLoginController extends Controller
{
    /**
     * Tampilkan halaman login pegawai.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->is_admin) {
            return redirect('/adminkuesioner');
        }

        if ($request->user()) {
            return redirect('/kuesioner');
        }

        return Inertia::render('Auth/Masuk');
    }

    /**
     * Proses login pegawai dan arahkan ke dashboard kuesioner.
     */
    public function store(PegawaiLoginRequest $request): RedirectResponse
    {
        $user = $request->authenticatePegawai();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/kuesioner');
    }

    /**
     * Logout user pegawai dari aplikasi.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/masuk');
    }
}
