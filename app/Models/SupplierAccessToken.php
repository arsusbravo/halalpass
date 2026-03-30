<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SupplierAccessToken extends Model
{
    protected $fillable = [
        'company_id',
        'supplier_id',
        'ingredient_id',
        'token',
        'expires_at',
        'used_at',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /* ----------------------------------------------------------------
     |  Relationships
     | ---------------------------------------------------------------- */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /* ----------------------------------------------------------------
     |  Accessors
     | ---------------------------------------------------------------- */

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at->isPast();
    }

    public function getIsUsedAttribute(): bool
    {
        return isset($this->used_at);
    }

    public function getIsValidAttribute(): bool
    {
        return !$this->is_expired && !$this->is_used;
    }

    /* ----------------------------------------------------------------
     |  Static Helpers
     | ---------------------------------------------------------------- */

    /**
     * Generate a new access token for a supplier.
     */
    public static function generateToken(
        int $companyId,
        int $supplierId,
        ?int $ingredientId = null,
        int $validDays = 30
    ): self {
        return self::create([
            'company_id' => $companyId,
            'supplier_id' => $supplierId,
            'ingredient_id' => $ingredientId,
            'token' => Str::random(64),
            'expires_at' => Carbon::now()->addDays($validDays),
        ]);
    }

    /* ----------------------------------------------------------------
     |  Scopes
     | ---------------------------------------------------------------- */

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', Carbon::now())
            ->whereNull('used_at');
    }
}