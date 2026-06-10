<?php

// Model periode kuesioner yang memiliki daftar pertanyaan dan response.

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kuesioner extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode',
        'judul',
        'deskripsi',
        'triwulan',
        'tahun',
        'copied_from_id',
        'status',
        'dibuka_pada',
        'ditutup_pada',
        'created_by',
        'excluded_pegawai_ids',
        'assign_all',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'triwulan' => 'integer',
            'tahun' => 'integer',
            'status' => 'string',
            'dibuka_pada' => 'datetime',
            'ditutup_pada' => 'datetime',
            'excluded_pegawai_ids' => 'array',
            'assign_all' => 'boolean',
        ];
    }

    /**
     * Relasi seluruh response pada kuesioner ini.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Relasi many-to-many dengan daftar pertanyaan.
     */
    public function pertanyaans(): BelongsToMany
    {
        return $this->belongsToMany(Pertanyaan::class, 'kuesioner_pertanyaan');
    }

    /**
     * Relasi sumber kuesioner jika dibuat dari fitur copy TW.
     */
    public function copiedFrom(): BelongsTo
    {
        return $this->belongsTo(Kuesioner::class, 'copied_from_id');
    }

    /**
     * Relasi salinan kuesioner yang dibuat dari kuesioner ini.
     */
    public function copies(): HasMany
    {
        return $this->hasMany(Kuesioner::class, 'copied_from_id');
    }

    /**
     * Relasi user admin pembuat kuesioner.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope kuesioner aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope filter kuesioner berdasarkan triwulan dan tahun.
     */
    public function scopeByTrwulan(Builder $query, int $tw, int $tahun): Builder
    {
        return $query->where('triwulan', $tw)->where('tahun', $tahun);
    }

    /**
     * Scope alias filter kuesioner berdasarkan triwulan dan tahun.
     */
    public function scopeByTriwulan(Builder $query, int $tw, int $tahun): Builder
    {
        return $this->scopeByTrwulan($query, $tw, $tahun);
    }

    /**
     * Cek apakah kuesioner sedang aktif.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Cek apakah kuesioner sudah ditutup.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Accessor label triwulan yang ramah dibaca.
     */
    public function getLabelTriwulanAttribute(): string
    {
        return match ((int) $this->triwulan) {
            1 => 'TW1 (Jan-Mar)',
            2 => 'TW2 (Apr-Jun)',
            3 => 'TW3 (Jul-Sep)',
            4 => 'TW4 (Okt-Des)',
            default => 'TW tidak valid',
        };
    }
}
