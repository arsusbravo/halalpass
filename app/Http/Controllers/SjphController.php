<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Facility;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\SjphDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SjphController extends Controller
{
    public function show(Request $request, Facility $facility): Response
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($facility->company_id !== $companyId, 403);

        $document = SjphDocument::where('company_id', $companyId)
            ->where('facility_id', $facility->id)
            ->where('status', '!=', 'archived')
            ->latest()
            ->first();

        $teamMembers = [];
        $trainings = [];

        if ($document) {
            $raw = $document->tim_manajemen_halal;
            if ($raw) {
                $decoded = json_decode($raw, true);
                $teamMembers = $decoded['members'] ?? [];
            }
            $raw = $document->pelatihan_edukasi;
            if ($raw) {
                $decoded = json_decode($raw, true);
                $trainings = $decoded['trainings'] ?? [];
            }
        }

        $ingredients = Ingredient::where('company_id', $companyId)->count();
        $ingredientsWithCert = Ingredient::where('company_id', $companyId)
            ->whereNotNull('sh_number')->where('sh_number', '!=', '')->count();
        $products = Product::where('company_id', $companyId)->active()->count();

        return Inertia::render('Sjph/Show', [
            'facility' => $facility,
            'document' => $document,
            'teamMembers' => $teamMembers,
            'trainings' => $trainings,
            'stats' => [
                'ingredients' => $ingredients,
                'ingredients_with_cert' => $ingredientsWithCert,
                'products' => $products,
            ],
        ]);
    }

    public function save(Request $request, Facility $facility)
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($facility->company_id !== $companyId, 403);

        $validated = $request->validate([
            'team_members' => 'nullable|array',
            'team_members.*.name' => 'nullable|string|max:255',
            'team_members.*.position' => 'nullable|string|max:255',
            'team_members.*.role' => 'nullable|string|max:255',
            'trainings' => 'nullable|array',
            'trainings.*.date' => 'nullable|string|max:20',
            'trainings.*.topic' => 'nullable|string|max:255',
            'trainings.*.provider' => 'nullable|string|max:255',
            'trainings.*.attendees' => 'nullable|string|max:255',
        ]);

        $document = SjphDocument::firstOrCreate(
            [
                'company_id' => $companyId,
                'facility_id' => $facility->id,
                'status' => 'draft',
            ],
            ['version' => 1]
        );

        if (!in_array($document->status, ['draft', 'in_review'])) {
            $document = SjphDocument::create([
                'company_id' => $companyId,
                'facility_id' => $facility->id,
                'status' => 'draft',
                'version' => ($document->version ?? 1) + 1,
            ]);
        }

        $document->tim_manajemen_halal = json_encode(['members' => $validated['team_members'] ?? []]);
        $document->pelatihan_edukasi = json_encode(['trainings' => $validated['trainings'] ?? []]);
        $document->save();

        return back()->with('success', __('SJPH data saved.'));
    }

    public function generate(Request $request, Facility $facility)
    {
        $companyId = $request->user()->activeCompanyId();
        abort_if($facility->company_id !== $companyId, 403);

        // Validate required company data
        $company = Company::findOrFail($companyId);
        $missing = [];
        if (empty($company->npwp)) $missing[] = 'NPWP';
        if (empty($company->pic_name)) $missing[] = __('Person In Charge');
        if (empty($company->signature_path)) $missing[] = __('Director Signature');

        if (count($missing) > 0) {
            return redirect()->route('company-profile.show')
                ->with('error', __('Please complete your company profile first: :fields', [
                    'fields' => implode(', ', $missing),
                ]));
        }

        $pdf = $this->buildPdf($companyId, $facility);

        // Mark as approved
        $document = SjphDocument::where('company_id', $companyId)
            ->where('facility_id', $facility->id)
            ->where('status', '!=', 'archived')
            ->latest()
            ->first();

        if ($document) {
            $document->update(['status' => 'approved']);
        } else {
            SjphDocument::create([
                'company_id' => $companyId,
                'facility_id' => $facility->id,
                'status' => 'approved',
                'version' => 1,
            ]);
        }

        $filename = 'SJPH_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $facility->name) . '_' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Build the SJPH PDF for a facility.
     * Public so AuditExportService can call it.
     */
    public function buildPdf(int $companyId, Facility $facility)
    {
        $company = Company::findOrFail($companyId);
        $facilityAddress = implode(', ', array_filter([$facility->address, $facility->city, $facility->province]));
        $ingredients = Ingredient::where('company_id', $companyId)->orderBy('name')->get();
        $products = Product::where('company_id', $companyId)->active()->with('ingredients')->orderBy('name')->get();

        $document = SjphDocument::where('company_id', $companyId)
            ->where('facility_id', $facility->id)
            ->where('status', '!=', 'archived')
            ->latest()
            ->first();

        $teamMembers = [];
        $trainings = [];
        if ($document) {
            $teamMembers = json_decode($document->tim_manajemen_halal ?? '{}', true)['members'] ?? [];
            $trainings = json_decode($document->pelatihan_edukasi ?? '{}', true)['trainings'] ?? [];
        }

        // Get signature as base64 for dompdf (can't load URLs)
        $signaturePath = null;
        if ($company->signature_path && Storage::disk('public')->exists($company->signature_path)) {
            $absolutePath = Storage::disk('public')->path($company->signature_path);
            $mime = mime_content_type($absolutePath);
            $base64 = base64_encode(file_get_contents($absolutePath));
            $signaturePath = "data:{$mime};base64,{$base64}";
        }

        return Pdf::loadView('pdfs.sjph', [
            'company' => $company,
            'facility' => $facility,
            'facilityAddress' => $facilityAddress,
            'ingredients' => $ingredients,
            'products' => $products,
            'teamMembers' => $teamMembers,
            'trainings' => $trainings,
            'signaturePath' => $signaturePath,
        ])->setPaper('a4');
    }
}