<?php

namespace App\Console\Commands;

use App\Services\HalalHealthScoreService;
use Illuminate\Console\Command;

class RecalculateHalalScores extends Command
{
    protected $signature = 'halal:recalculate
                            {--company= : Limit to a specific company ID}
                            {--product= : Recalculate for a single product ID}';

    protected $description = 'Recalculate Halal Health Scores for all active products';

    public function handle(HalalHealthScoreService $scoreService): int
    {
        $productId = $this->option('product');
        $companyId = $this->option('company');

        if ($productId) {
            $product = \App\Models\Product::find($productId);

            if (!$product) {
                $this->error("Product #{$productId} not found.");
                return self::FAILURE;
            }

            $result = $scoreService->calculateAndSave($product);

            $this->info("Product: {$product->name}");
            $this->info("Score: {$result->score}/100 — Status: {$result->status}");
            $this->newLine();

            $this->table(
                ['Ingredient', 'Score', 'Status', 'Certificate', 'Expiry', 'Days Left'],
                array_map(fn ($d) => [
                    $d['name'],
                    $d['score'],
                    $d['status'],
                    $d['certificate_sh'] ?? 'NONE',
                    $d['expiry_date'] ?? '-',
                    $d['days_until_expiry'] ?? '-',
                ], $result->ingredientDetails)
            );

            return self::SUCCESS;
        }

        $companyIds = $companyId
            ? [(int) $companyId]
            : \App\Models\Company::active()->pluck('id')->toArray();

        $totalProducts = 0;

        foreach ($companyIds as $id) {
            $this->info("Company #{$id}:");

            $results = $scoreService->recalculateForCompany($id);
            $totalProducts += count($results);

            foreach ($results as $result) {
                $emoji = match ($result->status) {
                    'compliant' => '✅',
                    'at_risk' => '⚠️',
                    'non_compliant' => '❌',
                    default => '❓',
                };

                $this->line("  {$emoji} Product #{$result->product_id}: {$result->score}/100 ({$result->status})");
            }

            $summary = $scoreService->getCompanySummary($id);
            $this->newLine();
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Products', $summary['total_products']],
                    ['Compliant', $summary['compliant']],
                    ['At Risk', $summary['at_risk']],
                    ['Non-Compliant', $summary['non_compliant']],
                    ['Pending', $summary['pending']],
                    ['Average Score', $summary['average_score'] . '/100'],
                ]
            );
        }

        $this->info("{$totalProducts} product(s) recalculated across " . count($companyIds) . " company(ies).");

        return self::SUCCESS;
    }
}