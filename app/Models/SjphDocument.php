<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SjphDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'facility_id',
        'version',
        'status',
        'kebijakan_halal',
        'tim_manajemen_halal',
        'pelatihan_edukasi',
        'bahan',
        'produk',
        'fasilitas_produksi',
        'prosedur_aktivitas_kritis',
        'kemampuan_telusur',
        'penanganan_produk_tidak_halal',
        'audit_internal',
        'kaji_ulang_manajemen',
        'document_path',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'kebijakan_halal' => 'array',
        'tim_manajemen_halal' => 'array',
        'pelatihan_edukasi' => 'array',
        'bahan' => 'array',
        'produk' => 'array',
        'fasilitas_produksi' => 'array',
        'prosedur_aktivitas_kritis' => 'array',
        'kemampuan_telusur' => 'array',
        'penanganan_produk_tidak_halal' => 'array',
        'audit_internal' => 'array',
        'kaji_ulang_manajemen' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * The 11 SJPH criteria keys in order.
     */
    public const CRITERIA = [
        'kebijakan_halal',
        'tim_manajemen_halal',
        'pelatihan_edukasi',
        'bahan',
        'produk',
        'fasilitas_produksi',
        'prosedur_aktivitas_kritis',
        'kemampuan_telusur',
        'penanganan_produk_tidak_halal',
        'audit_internal',
        'kaji_ulang_manajemen',
    ];

    /**
     * Human-readable labels for each criterion.
     */
    public const CRITERIA_LABELS = [
        'kebijakan_halal' => 'Kebijakan Halal',
        'tim_manajemen_halal' => 'Tim Manajemen Halal',
        'pelatihan_edukasi' => 'Pelatihan dan Edukasi',
        'bahan' => 'Bahan',
        'produk' => 'Produk',
        'fasilitas_produksi' => 'Fasilitas Produksi',
        'prosedur_aktivitas_kritis' => 'Prosedur Aktivitas Kritis',
        'kemampuan_telusur' => 'Kemampuan Telusur',
        'penanganan_produk_tidak_halal' => 'Penanganan Produk Tidak Memenuhi Kriteria',
        'audit_internal' => 'Audit Internal',
        'kaji_ulang_manajemen' => 'Kaji Ulang Manajemen',
    ];

    /* ----------------------------------------------------------------
     |  Relationships
     | ---------------------------------------------------------------- */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /* ----------------------------------------------------------------
     |  Accessors
     | ---------------------------------------------------------------- */

    /**
     * Calculate completion percentage based on how many criteria are filled.
     */
    public function getCompletionPercentageAttribute(): int
    {
        $filled = 0;

        foreach (self::CRITERIA as $criterion) {
            if (!empty($this->{$criterion})) {
                $filled++;
            }
        }

        return (int) round(($filled / count(self::CRITERIA)) * 100);
    }

    /**
     * Get list of criteria that are still empty.
     */
    public function getIncompleteCriteriaAttribute(): array
    {
        $incomplete = [];

        foreach (self::CRITERIA as $criterion) {
            if (empty($this->{$criterion})) {
                $incomplete[] = $criterion;
            }
        }

        return $incomplete;
    }

    /* ----------------------------------------------------------------
     |  Scopes
     | ---------------------------------------------------------------- */

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}