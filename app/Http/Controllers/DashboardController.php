<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Ingredient;
use App\Models\Product;
use App\Services\CertificateService;
use App\Services\HalalHealthScoreService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
        HalalHealthScoreService $scoreService,
        CertificateService $certificateService
    ): Response {
        $companyId = $request->user()->activeCompanyId();

        if (!$companyId) {
            return Inertia::render('Dashboard', [
                'productSummary' => null,
                'certSummary' => null,
                'expiringSoon' => [],
                'onboarding' => null,
            ]);
        }

        $productSummary = $scoreService->getCompanySummary($companyId);
        $certSummary = $certificateService->getStatusSummary($companyId);
        $expiringSoon = $certificateService->getExpiringSoon($companyId, 90);

        $onboarding = [
            'facilities' => Facility::where('company_id', $companyId)->count(),
            'ingredients' => Ingredient::where('company_id', $companyId)->count(),
            'ingredients_with_cert' => Ingredient::where('company_id', $companyId)
                ->whereNotNull('sh_number')
                ->count(),
            'products' => Product::where('company_id', $companyId)->count(),
        ];

        return Inertia::render('Dashboard', [
            'productSummary' => $productSummary,
            'certSummary' => $certSummary,
            'expiringSoon' => $expiringSoon,
            'onboarding' => $onboarding,
        ]);
    }
}