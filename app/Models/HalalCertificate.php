<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class HalalCertificate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'ingredient_id',
        'sh_number',
        'issuing_body',
        'issuing_body_name',
        'issue_date',
        'expiry_date',
        'document_path',
        'original_filename',
        'status',
        'notes',
        'uploaded_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    protected $appends = ['days_until_expiry', 'is_expired', 'is_expiring_soon'];

    /* ----------------------------------------------------------------
     |  Relationships
     | ---------------------------------------------------------------- */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /* ----------------------------------------------------------------
     |  Accessors
     | ---------------------------------------------------------------- */

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return !$this->is_expired
            && $this->expiry_date->isBefore(Carbon::now()->addDays(90));
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) Carbon::now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Compute the actual status based on expiry_date.
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->is_expiring_soon) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    /* ----------------------------------------------------------------
     |  Scopes
     | ---------------------------------------------------------------- */

    public function scopeValid($query)
    {
        return $query->where('status', 'valid');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('status', 'expiring_soon');
    }

    public function scopeExpiringWithinDays($query, int $days)
    {
        return $query->where('expiry_date', '<=', Carbon::now()->addDays($days))
            ->where('expiry_date', '>', Carbon::now());
    }
}