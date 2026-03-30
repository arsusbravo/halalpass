<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductIngredient extends Pivot
{
    protected $table = 'product_ingredients';

    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'ingredient_id',
        'percentage',
        'is_critical',
        'usage_purpose',
        'sort_order',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
        'is_critical' => 'boolean',
        'sort_order' => 'integer',
    ];

    /* ----------------------------------------------------------------
     |  Relationships
     | ---------------------------------------------------------------- */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}