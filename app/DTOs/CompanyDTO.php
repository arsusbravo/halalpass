<?php

namespace App\DTOs;

class CompanyDTO
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $npwp = null,
        public readonly ?string $bpjph_registration_number = null,
        public readonly ?string $address = null,
        public readonly ?string $city = null,
        public readonly ?string $province = null,
        public readonly ?string $postal_code = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $pic_name = null,
        public readonly ?string $pic_phone = null,
        public readonly string $status = 'active',
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            npwp: $data['npwp'] ?? null,
            bpjph_registration_number: $data['bpjph_registration_number'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            province: $data['province'] ?? null,
            postal_code: $data['postal_code'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            pic_name: $data['pic_name'] ?? null,
            pic_phone: $data['pic_phone'] ?? null,
            status: $data['status'] ?? 'active',
        );
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($v) => $v !== null);
    }
}