<?php

// Model response penilaian dari satu penilai kepada satu pegawai target.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Response extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kuesioner_id',
        'penilai_id',
        'target_id',
        'status',
        'submitted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
            'submitted_at' => 'datetime',
        ];
    }

    /**
     * Relasi kuesioner yang sedang dijawab.
     */
    public function kuesioner(): BelongsTo
    {
        return $this->belongsTo(Kuesioner::class);
    }

    /**
     * Relasi user yang menjadi penilai.
     */
    public function penilai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penilai_id');
    }

    /**
     * Relasi pegawai yang dinilai.
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'target_id');
    }

    /**
     * Relasi semua jawaban dalam response ini.
     */
    public function jawabans(): HasMany
    {
        return $this->hasMany(ResponseJawaban::class);
    }

    /**
     * Cek apakah seluruh pertanyaan aktif sudah dijawab.
     */
    public function isComplete(): bool
    {
        $totalPertanyaan = $this->kuesioner
            ? $this->kuesioner->pertanyaans()->active()->count()
            : 0;

        if ($totalPertanyaan === 0) {
            return false;
        }

        $totalJawaban = $this->jawabans()
            ->whereHas('pertanyaan', fn ($query) => $query->active())
            ->count();

        return $totalJawaban >= $totalPertanyaan;
    }

    /**
     * Accessor persentase progress jawaban dalam response.
     */
    public function getProgressAttribute(): int
    {
        $totalPertanyaan = $this->kuesioner
            ? $this->kuesioner->pertanyaans()->active()->count()
            : 0;

        if ($totalPertanyaan === 0) {
            return 0;
        }

        $totalJawaban = $this->jawabans()
            ->whereHas('pertanyaan', fn ($query) => $query->active())
            ->count();

        return (int) round(($totalJawaban / $totalPertanyaan) * 100);
    }
}
