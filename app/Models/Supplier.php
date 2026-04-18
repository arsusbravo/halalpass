<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AutoGeneratesCode;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, AutoGeneratesCode;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'address',
        'city',
        'country',
        'phone',
        'email',
        'pic_name',
        'pic_phone',
        'status',
        'notes',
    ];

    /* ----------------------------------------------------------------
     |  Relationships
     | ---------------------------------------------------------------- */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    public function accessTokens(): HasMany
    {
        return $this->hasMany(SupplierAccessToken::class);
    }

    /* ----------------------------------------------------------------
     |  Scopes
     | ---------------------------------------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}