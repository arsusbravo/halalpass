<?php

namespace App\DTOs;

class HalalHealthScoreResult
{
    /**
     * @param array<int, array{ingredient_id: int, name: string, score: int, status: string, certificate_sh: string|null, expiry_date: string|null, days_until_expiry: int|null}> $ingredientDetails
     */
    public function __construct(
        public readonly int $product_id,
        public readonly int $score,
        public readonly string $status,
        public readonly int $total_ingredients,
        public readonly int $compliant_count,
        public readonly int $at_risk_count,
        public readonly int $non_compliant_count,
        public readonly int $missing_count,
        public readonly array $ingredientDetails = [],
    ) {}

    public function toArray(): array
    {
        return [
            'product_id' => $this->product_id,
            'score' => $this->score,
            'status' => $this->status,
            'total_ingredients' => $this->total_ingredients,
            'compliant_count' => $this->compliant_count,
            'at_risk_count' => $this->at_risk_count,
            'non_compliant_count' => $this->non_compliant_count,
            'missing_count' => $this->missing_count,
            'ingredient_details' => $this->ingredientDetails,
        ];
    }
}