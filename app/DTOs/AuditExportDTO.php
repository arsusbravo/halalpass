<?php

namespace App\DTOs;

class AuditExportDTO
{
    public function __construct(
        public readonly int $company_id,
        public readonly ?array $product_ids = null,
        public readonly ?array $facility_ids = null,
        public readonly bool $include_certificates = true,
        public readonly bool $include_material_matrix = true,
    ) {}

    /**
     * Legacy factory method — kept for backward compatibility.
     */
    public static function fromRequest(array $data, int $companyId): self
    {
        return new self(
            company_id: $companyId,
            product_ids: $data['product_ids'] ?? null,
            facility_ids: $data['facility_ids'] ?? null,
            include_certificates: $data['include_certificates'] ?? true,
            include_material_matrix: $data['include_material_matrix'] ?? true,
        );
    }
}