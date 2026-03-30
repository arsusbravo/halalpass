<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Facility;
use App\Models\HalalCertificate;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Supplier;
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

        $company = Company::find($companyId);
        $onboarding = [
            'company_profile' => !empty($company->address) && !empty($company->npwp),
            'facilities' => Facility::where('company_id', $companyId)->count(),
            'suppliers' => Supplier::where('company_id', $companyId)->count(),
            'ingredients' => Ingredient::where('company_id', $companyId)->count(),
            'certificates' => HalalCertificate::where('company_id', $companyId)->count(),
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