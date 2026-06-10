<?php

// Model pertanyaan penilaian yang berada di dalam satu kuesioner.

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pertanyaan extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'isi',
        'urutan',
        'poin_min',
        'poin_max',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'poin_min' => 'integer',
            'poin_max' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Accessor untuk teks lengkap pertanyaan (menggabungkan judul dan isi).
     * Berguna untuk backward compatibility.
     */
    public function getTeksAttribute(): string
    {
        return $this->judul . "\n\n" . $this->isi;
    }

    /**
     * Relasi jawaban response untuk pertanyaan ini.
     */
    public function responseJawabans(): HasMany
    {
        return $this->hasMany(ResponseJawaban::class);
    }

    /**
     * Relasi many-to-many dengan daftar kuesioner.
     */
    public function kuesioners(): BelongsToMany
    {
        return $this->belongsToMany(Kuesioner::class, 'kuesioner_pertanyaan');
    }

    /**
     * Scope pertanyaan yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
