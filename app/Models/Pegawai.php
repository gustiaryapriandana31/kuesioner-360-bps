<?php

// Model pegawai yang menjadi target penilaian dalam Kuesioner 360.

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Pegawai extends Model
{
    use HasFactory, SoftDeletes;

    public const EXCLUDED_ASSESSMENT_NIPS = [
        '198211122006021001',
    ];

    protected static function booted(): void
    {
        $cleanupExclusions = function (Pegawai $pegawai) {
            $pegawaiId = $pegawai->id;
            Kuesioner::query()
                ->whereJsonContains('excluded_pegawai_ids', $pegawaiId)
                ->get()
                ->each(function (Kuesioner $kuesioner) use ($pegawaiId) {
                    $excludedIds = $kuesioner->excluded_pegawai_ids ?: [];
                    $kuesioner->excluded_pegawai_ids = array_values(array_diff($excludedIds, [$pegawaiId]));
                    $kuesioner->save();
                });
        };

        static::deleted($cleanupExclusions);

        if (method_exists(static::class, 'forceDeleted')) {
            static::forceDeleted($cleanupExclusions);
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'nip',
        'nama',
        'jabatan',
        'departemen',
        'foto',
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
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi akun user milik pegawai.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi response ketika pegawai menjadi target penilaian.
     */
    public function responsesAsTarget(): HasMany
    {
        return $this->hasMany(Response::class, 'target_id');
    }

    /**
     * Accessor URL foto pegawai atau avatar default.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return Storage::disk('public')->url($this->foto);
        }

        return asset('images/default-avatar.svg');
    }

    /**
     * Scope pegawai yang masih aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pegawai yang boleh muncul sebagai target penilaian.
     */
    public function scopeAssessable(Builder $query): Builder
    {
        return $query->whereNotIn('nip', self::EXCLUDED_ASSESSMENT_NIPS);
    }

    /**
     * Cari pegawai berdasarkan nama dengan pencarian fuzzy bertingkat.
     */
    public static function cariByNama(string $nama): ?self
    {
        $namaBersih = trim(preg_replace('/\s+/', ' ', $nama));

        // Level 1: exact match case-insensitive
        $p = self::whereRaw('LOWER(nama) = ?', [mb_strtolower($namaBersih)])->first();
        if ($p) return $p;

        // Level 2: normalisasi spasi di sekitar tanda baca lalu match
        try {
            $namaNorm = trim(preg_replace('/\s+/', ' ', preg_replace('/\s*([,.])\s*/', '$1 ', $namaBersih)));
            $p = self::whereRaw(
                'LOWER(TRIM(REGEXP_REPLACE(nama, "\\s*([,.])\\s*", "$1 "))) = ?',
                [mb_strtolower($namaNorm)]
            )->first();
            if ($p) return $p;
        } catch (\Exception $e) {
             // Fallback for drivers that don't support REGEXP_REPLACE
        }

        // Level 3: LIKE pada nama dasar (sebelum koma pertama = nama tanpa gelar)
        $namaDasar = trim(explode(',', $namaBersih)[0]);
        if (!empty($namaDasar)) {
            $p = self::where('nama', 'LIKE', "%{$namaDasar}%")->first();
            if ($p) return $p;
        }

        // Level 4: similar_text >= 75%
        $semua = self::all();
        $best = null;
        $bestScore = 0;
        foreach ($semua as $candidate) {
            similar_text(mb_strtolower($namaBersih), mb_strtolower($candidate->nama), $pct);
            if ($pct >= 75 && $pct > $bestScore) {
                $bestScore = $pct;
                $best = $candidate;
            }
        }
        return $best;
    }
}
