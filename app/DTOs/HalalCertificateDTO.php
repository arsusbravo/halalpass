<?php

namespace App\DTOs;

class HalalCertificateDTO
{
    public function __construct(
        public readonly int $company_id,
        public readonly int $ingredient_id,
        public readonly string $sh_number,
        public readonly string $issuing_body,
        public readonly string $expiry_date,
        public readonly ?string $issuing_body_name = null,
        public readonly ?string $issue_date = null,
        public readonly ?string $document_path = null,
        public readonly ?string $original_filename = null,
        public readonly ?string $notes = null,
        public readonly ?int $uploaded_by = null,
    ) {}

    public static function fromRequest(array $data, int $companyId, ?int $uploadedBy = null): self
    {
        return new self(
            company_id: $companyId,
            ingredient_id: $data['ingredient_id'],
            sh_number: $data['sh_number'],
            issuing_body: $data['issuing_body'],
            expiry_date: $data['expiry_date'],
            issuing_body_name: $data['issuing_body_name'] ?? null,
            issue_date: $data['issue_date'] ?? null,
            document_path: $data['document_path'] ?? null,
            original_filename: $data['original_filename'] ?? null,
            notes: $data['notes'] ?? null,
            uploaded_by: $uploadedBy,
        );
    }

    public function toArray(): array
    {
        $fields = get_object_vars($this);
        $fields['status'] = 'valid'; // Default; will be computed by CertificateService

        return array_filter($fields, fn ($v) => $v !== null);
    }
}