<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'company_id', 'role'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the active company ID.
     * Owner uses session-based switching; others use their fixed company_id.
     */
    public function activeCompanyId(): ?int
    {
        if ($this->role === 'owner') {
            return session('active_company_id');
        }
    
        return $this->company_id;
    }

    // --- ROLE ACCESSORS ---
 
    public function getIsOwnerAttribute(): bool
    {
        return $this->role === 'owner';
    }
    
    public function getIsAdminAttribute(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }
    
    public function getIsManagerAttribute(): bool
    {
        return in_array($this->role, ['owner', 'admin', 'manager']);
    }
    
    // --- SCOPE ---
    
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
