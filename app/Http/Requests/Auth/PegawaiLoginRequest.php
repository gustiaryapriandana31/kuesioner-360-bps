<?php

// Form request untuk validasi login pegawai biasa melalui halaman /masuk.

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PegawaiLoginRequest extends FormRequest
{
    /**
     * Tentukan apakah request login pegawai boleh diproses.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi input login pegawai.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Ambil user pegawai valid berdasarkan credential request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticatePegawai(): User
    {
        $credentials = $this->validated();
        $username = $credentials['username'];

        $user = User::query()
            ->with('pegawai')
            ->where(function ($query) use ($username) {
                $query->where('email', $username)
                      ->orWhere('email', 'like', $username . '@%');
            })
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password tidak sesuai.',
            ]);
        }

        if ($user->is_admin) {
            throw ValidationException::withMessages([
                'username' => 'Akun admin harus masuk melalui halaman admin.',
            ]);
        }

        if (! $user->pegawai || ! $user->pegawai->is_active) {
            throw ValidationException::withMessages([
                'username' => 'Akun ini belum terhubung dengan data pegawai aktif.',
            ]);
        }

        return $user;
    }
}
