<?php

namespace App\DTOs;

class SupplierDTO
{
    public function __construct(
        public readonly int $company_id,
        public readonly string $name,
        public readonly ?string $code = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly string $country = 'ID',
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $pic_name = null,
        public readonly ?string $pic_phone = null,
        public readonly string $status = 'active',
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data, int $companyId): self
    {
        return new self(
            company_id: $companyId,
            name: $data['name'],
            code: $data['code'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            country: $data['country'] ?? 'ID',
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            pic_name: $data['pic_name'] ?? null,
            pic_phone: $data['pic_phone'] ?? null,
            status: $data['status'] ?? 'active',
            notes: $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}