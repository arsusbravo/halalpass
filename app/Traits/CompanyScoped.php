<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Auto-scope model queries to the authenticated user's company.
 *
 * Add `use CompanyScoped;` to any model that has a company_id column.
 * This applies a global scope so you never accidentally query across tenants.
 */
trait CompanyScoped
{
    public static function bootCompanyScoped(): void
    {
        // Auto-scope all queries to current company
        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = Auth::user()?->company_id;

            if ($companyId) {
                $builder->where($builder->getModel()->getTable() . '.company_id', $companyId);
            }
        });

        // Auto-set company_id on creation
        static::creating(function ($model) {
            if (empty($model->company_id)) {
                $model->company_id = Auth::user()?->company_id;
            }
        });
    }

    /**
     * Query without the company scope (for admin/system operations).
     */
    public static function withoutCompanyScope(): Builder
    {
        return static::withoutGlobalScope('company');
    }
}