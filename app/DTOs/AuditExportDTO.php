<?php

namespace App\DTOs;

class AuditExportDTO
{
    /**
     * @param int[]|null $product_ids
     * @param int[]|null $facility_ids
     */
    public function __construct(
        public readonly int $company_id,
        public readonly ?array $product_ids = null,
        public readonly ?array $facility_ids = null,
        public readonly bool $include_certificates = true,
        public readonly bool $include_material_matrix = true,
        public readonly bool $include_sjph = false,
        public readonly ?string $export_format = 'xlsx',
    ) {}

    public static function fromRequest(array $data, int $companyId): self
    {
        return new self(
            company_id: $companyId,
            product_ids: $data['product_ids'] ?? null,
            facility_ids: $data['facility_ids'] ?? null,
            include_certificates: (bool) ($data['include_certificates'] ?? true),
            include_material_matrix: (bool) ($data['include_material_matrix'] ?? true),
            include_sjph: (bool) ($data['include_sjph'] ?? false),
            export_format: $data['export_format'] ?? 'xlsx',
        );
    }
}