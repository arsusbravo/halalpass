<?php

namespace App\DTOs;

class ProductIngredientDTO
{
    public function __construct(
        public readonly int $ingredient_id,
        public readonly ?float $percentage = null,
        public readonly bool $is_critical = false,
        public readonly ?string $usage_purpose = null,
        public readonly int $sort_order = 0,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            ingredient_id: $data['ingredient_id'],
            percentage: isset($data['percentage']) ? (float) $data['percentage'] : null,
            is_critical: (bool) ($data['is_critical'] ?? false),
            usage_purpose: $data['usage_purpose'] ?? null,
            sort_order: (int) ($data['sort_order'] ?? 0),
        );
    }

    public function toPivotArray(): array
    {
        return [
            'percentage' => $this->percentage,
            'is_critical' => $this->is_critical,
            'usage_purpose' => $this->usage_purpose,
            'sort_order' => $this->sort_order,
        ];
    }
}