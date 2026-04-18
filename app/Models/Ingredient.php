<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AutoGeneratesCode;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes, AutoGeneratesCode;

    protected $fillable = [
        'company_id',
        'parent_id',
        'supplier_id',
        'name',
        'code',
        'type',
        'origin_country',
        'brand',
        'manufacturer',
        'category',
        'specifications',
        'notes',
        'halal_risk_level',
        'sh_number',
        'cert_issue_date'
    ];

    protected $casts = [
        'specifications' => 'array',
    ];

    protected $appends = ['cert_status'];

    /* ----------------------------------------------------------------
     |  Relationships
     | ---------------------------------------------------------------- */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'parent_id');
    }

    /**
     * Recursive: children and all their descendants.
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function halalCertificates(): HasMany
    {
        return $this->hasMany(HalalCertificate::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
            ->withPivot('percentage', 'is_critical', 'usage_purpose', 'sort_order')
            ->withTimestamps();
    }

    /* ----------------------------------------------------------------
     |  Accessors
     | ---------------------------------------------------------------- */

    public function getIsCompositeAttribute(): bool
    {
        return $this->type === 'composite';
    }

    /**
     * Get the latest valid certificate for this ingredient.
     */
    public function getActiveCertificateAttribute(): ?HalalCertificate
    {
        return $this->halalCertificates
            ->where('status', 'valid')
            ->sortByDesc('expiry_date')
            ->first();
    }

    /* ----------------------------------------------------------------
     |  Scopes
     | ---------------------------------------------------------------- */

    public function scopeSimple($query)
    {
        return $query->where('type', 'simple');
    }

    public function scopeComposite($query)
    {
        return $query->where('type', 'composite');
    }

    public function scopeRootLevel($query)
    {
        return $query->whereNull('parent_id');
    }
    
    public function getCertStatusAttribute(): ?string
    {
        $riskLevel = $this->halal_risk_level ?? 'medium_risk';

        if ($riskLevel === 'no_risk') {
            return 'valid';
        }

        // Has SH number = valid for life (GR 42/2024)
        if ($this->sh_number) {
            return 'valid';
        }

        // Low risk without cert = ok
        if ($riskLevel === 'low_risk') {
            return null;
        }

        // Medium/high risk without cert = missing
        return 'missing';
    }
}