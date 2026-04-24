<?php

namespace App\DTOs;

use App\Models\Facility;

class FacilityDTO
{
    public function __construct(
        public readonly int $company_id,
        public readonly string $name,
        public readonly string $address,
        public readonly string $city,
        public readonly string $province,
        public readonly ?string $code = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $phone = null,
        public readonly ?string $pic_name = null,
        public readonly ?string $production_capacity = null,
        public readonly string $sjph_status = 'not_started',
        public readonly string $status = 'active',
    ) {}

    public static function fromRequest(array $data, int $companyId): self
    {
        return new self(
            company_id: $companyId,
            name: $data['name'],
            address: $data['address'],
            city: $data['city'],
            province: $data['province'],
            code: $data['code'] ?? Facility::generateCode('FCT', $companyId),
            postal_code: $data['postal_code'] ?? null,
            phone: $data['phone'] ?? null,
            pic_name: $data['pic_name'] ?? null,
            production_capacity: $data['production_capacity'] ?? null,
            sjph_status: $data['sjph_status'] ?? 'not_started',
            status: $data['status'] ?? 'active',
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}