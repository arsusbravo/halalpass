<?php

namespace App\Console\Commands;

use App\Services\CertificateService;
use App\Services\HalalHealthScoreService;
use App\Services\SupplierNotificationService;
use Illuminate\Console\Command;

class CheckCertificateExpiry extends Command
{
    protected $signature = 'halal:check-expiry
                            {--company= : Limit to a specific company ID}
                            {--notify : Send WhatsApp notifications to suppliers}
                            {--recalculate : Recalculate all product halal scores}';

    protected $description = 'Check all halal certificates, update statuses, optionally notify suppliers and recalculate scores';

    public function handle(
        CertificateService $certificateService,
        HalalHealthScoreService $scoreService,
        SupplierNotificationService $notificationService
    ): int {
        $companyId = $this->option('company');

        // If no specific company, get all active companies
        $companyIds = $companyId
            ? [(int) $companyId]
            : \App\Models\Company::active()->pluck('id')->toArray();

        foreach ($companyIds as $id) {
            $this->info("Processing company ID: {$id}");

            // 1. Refresh certificate statuses
            $changes = $certificateService->refreshStatuses($id);

            $this->table(
                ['Status', 'Count'],
                [
                    ['Valid', $changes['valid']],
                    ['Expiring Soon', $changes['expiring_soon']],
                    ['Expired', $changes['expired']],
                    ['Updated', $changes['updated']],
                    ['Total', $changes['total']],
                ]
            );

            // 2. Recalculate product scores if requested
            if ($this->option('recalculate')) {
                $this->info('Recalculating halal health scores...');
                $results = $scoreService->recalculateForCompany($id);

                foreach ($results as $result) {
                    $statusEmoji = match ($result->status) {
                        'compliant' => '✅',
                        'at_risk' => '⚠️',
                        'non_compliant' => '❌',
                        default => '❓',
                    };

                    $this->line("  {$statusEmoji} Product #{$result->product_id}: {$result->score}/100 ({$result->status})");
                }

                $this->info(count($results) . ' product(s) recalculated.');
            }

            // 3. Notify suppliers if requested
            if ($this->option('notify')) {
                $this->info('Sending supplier notifications...');
                $notifyResult = $notificationService->notifyExpiringSoon($id);

                $this->info(
                    "Notified {$notifyResult['total_suppliers_notified']} supplier(s) "
                    . "about {$notifyResult['total_certificates']} certificate(s)."
                );
            }

            $this->newLine();
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}