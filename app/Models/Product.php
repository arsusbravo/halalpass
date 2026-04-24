<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AutoGeneratesCode;

class Product extends Model
{
    use HasFactory, SoftDeletes, AutoGeneratesCode;

    protected $fillable = [
        'company_id',
        'facility_id',
        'name',
        'code',
        'brand',
        'description',
        'category',
        'halal_status',
        'halal_health_score',
        'halal_status_checked_at',
        'status',
    ];

    protected $casts = [
        'halal_health_score' => 'integer',
        'halal_status_checked_at' => 'date',
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

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')
            ->withPivot('percentage', 'is_critical', 'usage_purpose', 'sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Only ingredients marked as critical halal points (Titik Kritis).
     */
    public function criticalIngredients(): BelongsToMany
    {
        return $this->ingredients()->wherePivot('is_critical', true);
    }

    /* ----------------------------------------------------------------
     |  Accessors
     | ---------------------------------------------------------------- */

    public function getIsCompliantAttribute(): bool
    {
        return $this->halal_status === 'compliant';
    }

    public function getIsAtRiskAttribute(): bool
    {
        return $this->halal_status === 'at_risk';
    }

    /* ----------------------------------------------------------------
     |  Scopes
     | ---------------------------------------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompliant($query)
    {
        return $query->where('halal_status', 'compliant');
    }

    public function scopeNonCompliant($query)
    {
        return $query->where('halal_status', 'non_compliant');
    }

    public function scopeAtRisk($query)
    {
        return $query->where('halal_status', 'at_risk');
    }
}