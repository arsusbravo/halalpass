<?php

namespace App\Http\Controllers;

use App\DTOs\AuditExportDTO;
use App\Models\Facility;
use App\Models\Product;
use App\Services\AuditExportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditExportController extends Controller
{
    private AuditExportService $exportService;

    public function __construct(AuditExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $products = Product::where('company_id', $companyId)
            ->active()
            ->get(['id', 'name', 'code', 'halal_status', 'halal_health_score']);

        $facilities = Facility::where('company_id', $companyId)
            ->active()
            ->get(['id', 'name', 'code']);

        return Inertia::render('Export/Index', [
            'products' => $products,
            'facilities' => $facilities,
        ]);
    }

    public function generate(Request $request)
    {
        $companyId = $request->user()->activeCompanyId();

        $productIds = $request->query('products')
            ? explode(',', $request->query('products'))
            : null;

        $facilityIds = $request->query('facilities')
            ? explode(',', $request->query('facilities'))
            : null;

        $includeCerts = $request->boolean('include_certificates', true);
        $includeMatrix = $request->boolean('include_material_matrix', true);

        $dto = new AuditExportDTO(
            company_id: $companyId,
            product_ids: $productIds,
            facility_ids: $facilityIds,
            include_certificates: $includeCerts,
            include_material_matrix: $includeMatrix,
        );

        try {
            $zipPath = $this->exportService->generateExport($dto);

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', __('Export failed: :message', ['message' => $e->getMessage()]));
        }
    }
}