<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'npwp',
        'bpjph_registration_number',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'pic_name',
        'pic_phone',
        'status',
        'signature_path',
    ];

    /* ----------------------------------------------------------------
     |  Relationships
     | ---------------------------------------------------------------- */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function halalCertificates(): HasMany
    {
        return $this->hasMany(HalalCertificate::class);
    }

    public function sjphDocuments(): HasMany
    {
        return $this->hasMany(SjphDocument::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /* ----------------------------------------------------------------
     |  Scopes
     | ---------------------------------------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}