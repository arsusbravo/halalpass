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
            ->get(['id', 'name', 'code', 'halal_status']);

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
        $request->validate([
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
            'facility_ids' => 'nullable|array',
            'facility_ids.*' => 'exists:facilities,id',
            'include_certificates' => 'nullable|boolean',
            'include_material_matrix' => 'nullable|boolean',
        ]);

        $dto = AuditExportDTO::fromRequest(
            $request->all(),
            $request->user()->activeCompanyId()
        );

        try {
            $zipPath = $this->exportService->generateExport($dto);

            return response()->download($zipPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat export: ' . $e->getMessage());
        }
    }
}