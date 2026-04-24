<?php

namespace App\DTOs;

use App\Models\Product;

class ProductDTO
{
    /**
     * @param ProductIngredientDTO[] $ingredients
     */
    public function __construct(
        public readonly int $company_id,
        public readonly int $facility_id,
        public readonly string $name,
        public readonly ?string $code = null,
        public readonly ?string $brand = null,
        public readonly ?string $description = null,
        public readonly ?string $category = null,
        public readonly string $status = 'active',
        public readonly array $ingredients = [],
    ) {}

    public static function fromRequest(array $data, int $companyId): self
    {
        $ingredients = [];

        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            foreach ($data['ingredients'] as $ingredientData) {
                $ingredients[] = ProductIngredientDTO::fromRequest($ingredientData);
            }
        }

        return new self(
            company_id: $companyId,
            facility_id: $data['facility_id'],
            name: $data['name'],
            code: $data['code'] ?? Product::generateCode('PRD', $companyId),
            brand: $data['brand'] ?? null,
            description: $data['description'] ?? null,
            category: $data['category'] ?? null,
            status: $data['status'] ?? 'active',
            ingredients: $ingredients,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'company_id' => $this->company_id,
            'facility_id' => $this->facility_id,
            'name' => $this->name,
            'code' => $this->code,
            'brand' => $this->brand,
            'description' => $this->description,
            'category' => $this->category,
            'status' => $this->status,
        ], fn ($v) => $v !== null);
    }
}