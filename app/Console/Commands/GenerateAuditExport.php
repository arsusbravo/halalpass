<?php

namespace App\Console\Commands;

use App\DTOs\AuditExportDTO;
use App\Services\AuditExportService;
use Illuminate\Console\Command;

class GenerateAuditExport extends Command
{
    protected $signature = 'halal:export
                            {company : Company ID to export}
                            {--products= : Comma-separated product IDs (optional)}
                            {--facilities= : Comma-separated facility IDs (optional)}
                            {--no-certs : Exclude certificate files from ZIP}
                            {--no-matrix : Exclude material matrix}';

    protected $description = 'Generate an audit-ready ZIP export (Daftar Bahan + Matriks Bahan) for SIHALAL';

    public function handle(AuditExportService $exportService): int
    {
        $companyId = (int) $this->argument('company');

        $company = \App\Models\Company::find($companyId);
        if (!$company) {
            $this->error("Company #{$companyId} not found.");
            return self::FAILURE;
        }

        $productIds = $this->option('products')
            ? array_map('intval', explode(',', $this->option('products')))
            : null;

        $facilityIds = $this->option('facilities')
            ? array_map('intval', explode(',', $this->option('facilities')))
            : null;

        $dto = new AuditExportDTO(
            company_id: $companyId,
            product_ids: $productIds,
            facility_ids: $facilityIds,
            include_certificates: !$this->option('no-certs'),
            include_material_matrix: !$this->option('no-matrix'),
        );

        $this->info("Generating audit export for: {$company->name}");

        try {
            $zipPath = $exportService->generateExport($dto);

            $this->newLine();
            $this->info('Export generated successfully!');
            $this->line("File: {$zipPath}");
            $this->line("Size: " . $this->formatBytes(filesize($zipPath)));

        } catch (\Exception $e) {
            $this->error("Export failed: {$e->getMessage()}");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}