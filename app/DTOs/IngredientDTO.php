<?php

namespace App\DTOs;

class IngredientDTO
{
    /**
     * @param IngredientDTO[] $children
     */
    public function __construct(
        public readonly int $company_id,
        public readonly string $name,
        public readonly string $type = 'simple',
        public readonly ?int $parent_id = null,
        public readonly ?int $supplier_id = null,
        public readonly ?string $code = null,
        public readonly ?string $origin_country = null,
        public readonly ?string $brand = null,
        public readonly ?string $manufacturer = null,
        public readonly string $category = 'bahan_baku',
        public readonly ?array $specifications = null,
        public readonly ?string $notes = null,
        public readonly array $children = [],
        public readonly string $halal_risk_level = 'medium_risk',
    ) {}

    public static function fromRequest(array $data, int $companyId, ?int $parentId = null): self
    {
        $children = [];

        if (isset($data['children']) && is_array($data['children'])) {
            foreach ($data['children'] as $childData) {
                $children[] = self::fromRequest($childData, $companyId, null);
            }
        }

        return new self(
            company_id: $companyId,
            name: $data['name'],
            type: $data['type'] ?? 'simple',
            parent_id: $parentId,
            supplier_id: $data['supplier_id'] ?? null,
            code: $data['code'] ?? null,
            origin_country: $data['origin_country'] ?? null,
            brand: $data['brand'] ?? null,
            manufacturer: $data['manufacturer'] ?? null,
            category: $data['category'] ?? 'bahan_baku',
            specifications: $data['specifications'] ?? null,
            notes: $data['notes'] ?? null,
            children: $children,
        );
    }

    /**
     * Return only the fields for the ingredients table (no children).
     */
    public function toArray(): array
    {
        $fields = [
            'company_id' => $this->company_id,
            'name' => $this->name,
            'type' => $this->type,
            'parent_id' => $this->parent_id,
            'supplier_id' => $this->supplier_id,
            'code' => $this->code,
            'origin_country' => $this->origin_country,
            'brand' => $this->brand,
            'manufacturer' => $this->manufacturer,
            'category' => $this->category,
            'specifications' => $this->specifications,
            'notes' => $this->notes,
        ];

        return array_filter($fields, fn ($v) => $v !== null);
    }
}