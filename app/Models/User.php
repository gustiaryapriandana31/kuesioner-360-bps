<?php

// Model user aplikasi yang juga menjadi akun login pegawai dan admin.

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'nip',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Relasi profil pegawai yang terhubung ke akun user.
     */
    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class);
    }

    /**
     * Relasi response yang dibuat user sebagai penilai.
     */
    public function responsesAsPenilai(): HasMany
    {
        return $this->hasMany(Response::class, 'penilai_id');
    }

    /**
     * Relasi kuesioner yang dibuat oleh user admin.
     */
    public function kuesionersDibuat(): HasMany
    {
        return $this->hasMany(Kuesioner::class, 'created_by');
    }

    /**
     * Batasi akses panel Filament hanya untuk akun admin.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_admin;
    }
}
