<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\SjphDocument;
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

        $scoreService->recalculateForCompany($companyId);

        $facilities = Facility::where('company_id', $companyId)->active()->get();
        $products = Product::where('company_id', $companyId)->active()->get();
        $ingredients = Ingredient::where('company_id', $companyId)->get();

        // SJPH status per facility
        $sjphStatuses = [];
        foreach ($facilities as $facility) {
            $sjph = SjphDocument::where('company_id', $companyId)
                ->where('facility_id', $facility->id)
                ->where('status', '!=', 'archived')
                ->latest()
                ->first();

            $completion = 0;
            if ($sjph) {
                if ($sjph->status === 'approved') {
                    $completion = 100;
                } else {
                    $team = json_decode($sjph->tim_manajemen_halal ?? '{}', true);
                    $training = json_decode($sjph->pelatihan_edukasi ?? '{}', true);
                    $hasTeam = !empty($team['members']) && !empty($team['members'][0]['name']);
                    $hasTraining = !empty($training['trainings']) && !empty($training['trainings'][0]['date']);
                    $completion = ($hasTeam ? 50 : 0) + ($hasTraining ? 50 : 0);
                }
            }

            $sjphStatuses[] = [
                'facility_id' => $facility->id,
                'facility_name' => $facility->name,
                'sjph_exists' => $sjph !== null,
                'sjph_status' => $sjph?->status ?? 'not_started',
                'sjph_completion' => $completion,
                'sjph_approved' => $sjph?->status === 'approved',
            ];
        }

        // Ingredients missing SH number (medium/high risk only)
        $ingredientIssues = [];
        foreach ($ingredients as $ingredient) {
            $riskLevel = $ingredient->halal_risk_level ?? 'medium_risk';

            if (in_array($riskLevel, ['medium_risk', 'high_risk']) && empty($ingredient->sh_number)) {
                $ingredientIssues[] = [
                    'id' => $ingredient->id,
                    'name' => $ingredient->name,
                    'code' => $ingredient->code,
                    'risk_level' => $riskLevel,
                    'brand' => $ingredient->brand,
                ];
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

        // Readiness checks
        $requiresCert = $ingredients->filter(fn ($i) =>
            in_array($i->halal_risk_level ?? 'medium_risk', ['medium_risk', 'high_risk'])
        );
        $allCertsValid = $requiresCert->isEmpty() ||
            $requiresCert->every(fn ($i) => !empty($i->sh_number));

        $checks = [
            'has_facilities' => $facilities->count() > 0,
            'has_ingredients' => $ingredients->count() > 0,
            'has_products' => $products->count() > 0,
            'all_certs_valid' => $allCertsValid,
            'all_products_compliant' => count($productIssues) === 0,
            'any_sjph_approved' => collect($sjphStatuses)->contains(fn ($s) => $s['sjph_approved']),
        ];

        $totalChecks = count($checks);
        $passedChecks = count(array_filter($checks));
        $readinessPercentage = $totalChecks > 0 ? (int) round(($passedChecks / $totalChecks) * 100) : 0;

        $isReady = $checks['has_facilities']
            && $checks['has_ingredients']
            && $checks['has_products']
            && $checks['all_certs_valid']
            && $checks['all_products_compliant']
            && $checks['any_sjph_approved'];

        return Inertia::render('Certification/Readiness', [
            'isReady' => $isReady,
            'readinessPercentage' => $readinessPercentage,
            'checks' => $checks,
            'ingredientIssues' => $ingredientIssues,
            'productIssues' => $productIssues,
            'sjphStatuses' => $sjphStatuses,
        ]);
    }
}