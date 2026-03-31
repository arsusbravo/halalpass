<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\HalalCertificate;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\SjphDocument;
use App\Models\Supplier;
use App\Services\HalalHealthScoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CertificationReadinessController extends Controller
{
    public function __invoke(
        Request $request,
        HalalHealthScoreService $scoreService
    ): Response {
        $companyId = $request->user()->activeCompanyId();

        // Recalculate all scores to get fresh data
        $scoreService->recalculateForCompany($companyId);

        // Gather all readiness checks
        $facilities = Facility::where('company_id', $companyId)->active()->get();
        $products = Product::where('company_id', $companyId)->active()->get();
        $ingredients = Ingredient::where('company_id', $companyId)->with('halalCertificates')->get();
        $certificates = HalalCertificate::where('company_id', $companyId)->get();
        $suppliers = Supplier::where('company_id', $companyId)->active()->get();

        // SJPH status per facility
        $sjphStatuses = [];
        foreach ($facilities as $facility) {
            $sjph = SjphDocument::where('company_id', $companyId)
                ->where('facility_id', $facility->id)
                ->where('status', '!=', 'archived')
                ->latest()
                ->first();

            $sjphStatuses[] = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'sjph_exists' => $sjph !== null,
                'sjph_status' => $sjph?->status ?? 'not_started',
                'sjph_completion' => $sjph?->completion_percentage ?? 0,
                'sjph_approved' => $sjph?->status === 'approved',
            ];
        }

        // Ingredients needing certificates (medium_risk + high_risk without valid cert)
        $ingredientIssues = [];
        foreach ($ingredients as $ingredient) {
            $riskLevel = $ingredient->halal_risk_level ?? 'medium_risk';

            if (in_array($riskLevel, ['medium_risk', 'high_risk'])) {
                $bestCert = $ingredient->halalCertificates->sortByDesc('expiry_date')->first();
                $hasValidCert = $bestCert && !$bestCert->is_expired;

                if (!$hasValidCert) {
                    $ingredientIssues[] = [
                        'id' => $ingredient->id,
                        'name' => $ingredient->name,
                        'code' => $ingredient->code,
                        'risk_level' => $riskLevel,
                        'supplier' => $ingredient->supplier?->name,
                        'cert_status' => $bestCert ? 'expired' : 'missing',
                        'cert_expiry' => $bestCert?->expiry_date?->toDateString(),
                    ];
                }
            }
        }

        // Non-compliant products
        $productIssues = $products->where('halal_status', '!=', 'compliant')
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'halal_status' => $p->halal_status,
                'halal_health_score' => $p->halal_health_score,
            ])->values()->toArray();

        // Expiring certificates (within 90 days)
        $expiringSoon = $certificates->where('status', 'expiring_soon')
            ->map(fn ($c) => [
                'id' => $c->id,
                'sh_number' => $c->sh_number,
                'expiry_date' => $c->expiry_date->toDateString(),
                'days_until_expiry' => $c->days_until_expiry,
                'ingredient_name' => $c->ingredient?->name,
            ])->values()->toArray();

        // Overall readiness
        $checks = [
            'has_facilities' => $facilities->count() > 0,
            'has_suppliers' => $suppliers->count() > 0,
            'has_ingredients' => $ingredients->count() > 0,
            'has_products' => $products->count() > 0,
            'all_certs_valid' => count($ingredientIssues) === 0,
            'all_products_compliant' => count($productIssues) === 0,
            'no_expiring_certs' => count($expiringSoon) === 0,
            'all_sjph_approved' => collect($sjphStatuses)->every(fn ($s) => $s['sjph_approved']),
            'any_sjph_approved' => collect($sjphStatuses)->contains(fn ($s) => $s['sjph_approved']),
        ];

        $totalChecks = count($checks);
        $passedChecks = count(array_filter($checks));
        $readinessPercentage = $totalChecks > 0 ? (int) round(($passedChecks / $totalChecks) * 100) : 0;

        $isReady = $checks['has_facilities']
            && $checks['has_products']
            && $checks['all_certs_valid']
            && $checks['all_products_compliant']
            && $checks['any_sjph_approved'];

        return Inertia::render('Certification/Readiness', [
            'isReady' => $isReady,
            'readinessPercentage' => $readinessPercentage,
            'checks' => $checks,
            'stats' => [
                'facilities' => $facilities->count(),
                'suppliers' => $suppliers->count(),
                'ingredients' => $ingredients->count(),
                'products' => $products->count(),
                'certificates' => $certificates->count(),
                'products_compliant' => $products->where('halal_status', 'compliant')->count(),
                'products_total' => $products->count(),
            ],
            'ingredientIssues' => $ingredientIssues,
            'productIssues' => $productIssues,
            'expiringSoon' => $expiringSoon,
            'sjphStatuses' => $sjphStatuses,
        ]);
    }
}