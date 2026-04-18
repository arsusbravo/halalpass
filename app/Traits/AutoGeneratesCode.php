<?php

namespace App\Traits;

trait AutoGeneratesCode
{
    /**
     * Generate the next code for a model within a company.
     *
     * Usage: $code = Ingredient::generateCode('BHN', $companyId);
     *        → "BHN-001", "BHN-002", etc.
     */
    public static function generateCode(string $prefix, int $companyId): string
    {
        $lastCode = static::where('company_id', $companyId)
            ->where('code', 'like', "{$prefix}-%")
            ->orderByRaw("CAST(SUBSTRING(code, ?) AS UNSIGNED) DESC", [strlen($prefix) + 2])
            ->value('code');

        if ($lastCode && preg_match('/-(\d+)$/', $lastCode, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s-%03d', $prefix, $next);
    }
}