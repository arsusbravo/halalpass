<?php

namespace App\Services;

use App\DTOs\HalalHealthScoreResult;
use App\Models\HalalCertificate;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Support\Carbon;

class HalalHealthScoreService
{
    private const SCORE_VALID = 100;
    private const SCORE_EXPIRING_SOON = 70;
    private const SCORE_EXPIRED = 0;
    private const SCORE_MISSING = 0;

    private IngredientService $ingredientService;

    public function __construct(IngredientService $ingredientService)
    {
        $this->ingredientService = $ingredientService;
    }

    /**
     * Calculate the Halal Health Score for a single product.
     *
     * Walks the full ingredient tree (including sub-ingredients of composites),
     * checks every leaf ingredient's certificate, and returns a score + status.
     *
     * Weakest-link principle: one bad cert tanks the whole product.
     */
    public function calculateForProduct(Product $product): HalalHealthScoreResult
    {
        $product->load('ingredients.childrenRecursive');

        $leafIngredients = $this->collectAllLeafIngredients($product);
        $ingredientDetails = [];

        $compliantCount = 0;
        $atRiskCount = 0;
        $nonCompliantCount = 0;
        $missingCount = 0;

        foreach ($leafIngredients as $ingredient) {
            $certificate = $this->getBestCertificate($ingredient);
            $ingredientScore = $this->scoreIngredient($ingredient, $certificate);
            $ingredientStatus = $this->statusFromScore($ingredientScore);

            switch ($ingredientStatus) {
                case 'compliant':
                    $compliantCount++;
                    break;
                case 'at_risk':
                    $atRiskCount++;
                    break;
                case 'non_compliant':
                    $nonCompliantCount++;
                    break;
                case 'missing':
                    $missingCount++;
                    break;
            }

            $ingredientDetails[] = [
                'ingredient_id' => $ingredient->id,
                'name' => $ingredient->name,
                'score' => $ingredientScore,
                'status' => $ingredientStatus,
                'certificate_sh' => $certificate?->sh_number,
                'expiry_date' => $certificate?->expiry_date?->toDateString(),
                'days_until_expiry' => $certificate?->days_until_expiry,
            ];
        }

        $totalIngredients = count($leafIngredients);

        // Product score = minimum of all ingredient scores (weakest link)
        $productScore = $totalIngredients > 0
            ? min(array_column($ingredientDetails, 'score'))
            : 0;

        $productStatus = $this->productStatusFromScore($productScore);

        return new HalalHealthScoreResult(
            product_id: $product->id,
            score: $productScore,
            status: $productStatus,
            total_ingredients: $totalIngredients,
            compliant_count: $compliantCount,
            at_risk_count: $atRiskCount,
            non_compliant_count: $nonCompliantCount,
            missing_count: $missingCount,
            ingredientDetails: $ingredientDetails,
        );
    }

    /**
     * Calculate and persist the score for a product.
     */
    public function calculateAndSave(Product $product): HalalHealthScoreResult
    {
        $result = $this->calculateForProduct($product);

        $product->update([
            'halal_health_score' => $result->score,
            'halal_status' => $result->status,
            'halal_status_checked_at' => Carbon::now(),
        ]);

        return $result;
    }

    /**
     * Recalculate scores for all active products in a company.
     */
    public function recalculateForCompany(int $companyId): array
    {
        $products = Product::where('company_id', $companyId)
            ->active()
            ->get();

        $results = [];

        foreach ($products as $product) {
            $results[] = $this->calculateAndSave($product);
        }

        return $results;
    }

    /**
     * Get a company-wide dashboard summary.
     */
    public function getCompanySummary(int $companyId): array
    {
        $products = Product::where('company_id', $companyId)
            ->active()
            ->get();

        $total = $products->count();

        return [
            'total_products' => $total,
            'compliant' => $products->where('halal_status', 'compliant')->count(),
            'at_risk' => $products->where('halal_status', 'at_risk')->count(),
            'non_compliant' => $products->where('halal_status', 'non_compliant')->count(),
            'pending' => $products->where('halal_status', 'pending')->count(),
            'average_score' => $total > 0
                ? (int) round($products->avg('halal_health_score'))
                : 0,
        ];
    }

    // ----------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------

    /**
     * Collect all leaf-level (simple) ingredients for a product.
     * If a product uses a composite ingredient, we walk down to its leaves.
     *
     * @return Ingredient[]
     */
    private function collectAllLeafIngredients(Product $product): array
    {
        $leaves = [];

        foreach ($product->ingredients as $ingredient) {
            $leafIds = $this->ingredientService->getLeafIngredientIds($ingredient);
            $leafIngredients = Ingredient::whereIn('id', $leafIds)
                ->with('halalCertificates')
                ->get();

            foreach ($leafIngredients as $leaf) {
                $leaves[$leaf->id] = $leaf; // Deduplicate by ID
            }
        }

        return array_values($leaves);
    }

    /**
     * Get the best (most recent valid) certificate for an ingredient.
     */
    private function getBestCertificate(Ingredient $ingredient): ?HalalCertificate
    {
        return $ingredient->halalCertificates
            ->sortByDesc('expiry_date')
            ->first();
    }

    private function scoreIngredient(Ingredient $ingredient, ?HalalCertificate $certificate): int
    {
        $riskLevel = $ingredient->halal_risk_level ?? 'medium_risk';
    
        // Naturally halal — always compliant
        if ($riskLevel === 'no_risk') {
            return self::SCORE_VALID;
        }
    
        // Check inline certificate first (simplified flow)
        // Under GR 42/2024: if SH number exists, cert is valid for life
        if ($ingredient->sh_number) {
            return self::SCORE_VALID;
        }
    
        // Low risk — compliant without cert
        if ($riskLevel === 'low_risk') {
            // Check legacy certificate table as fallback
            if (!$certificate) return self::SCORE_VALID;
            if ($certificate->is_expired) return self::SCORE_EXPIRING_SOON;
            return self::SCORE_VALID;
        }
    
        // Medium and high risk — certificate required
        // Fall back to legacy certificate table
        if (!$certificate) return self::SCORE_MISSING;
        if ($certificate->is_expired) return self::SCORE_EXPIRED;
        if ($certificate->is_expiring_soon) return self::SCORE_EXPIRING_SOON;
        return self::SCORE_VALID;
    }

    /**
     * Convert an ingredient score to a readable status.
     */
    private function statusFromScore(int $score): string
    {
        if ($score === self::SCORE_VALID) {
            return 'compliant';
        }

        if ($score === self::SCORE_EXPIRING_SOON) {
            return 'at_risk';
        }

        if ($score === self::SCORE_MISSING) {
            return 'missing';
        }

        return 'non_compliant';
    }

    /**
     * Determine product halal status from its overall score.
     */
    private function productStatusFromScore(int $score): string
    {
        if ($score >= self::SCORE_VALID) {
            return 'compliant';
        }

        if ($score >= self::SCORE_EXPIRING_SOON) {
            return 'at_risk';
        }

        return 'non_compliant';
    }
}