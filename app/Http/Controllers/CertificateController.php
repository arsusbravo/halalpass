<?php

namespace App\Http\Controllers;

use App\DTOs\HalalCertificateDTO;
use App\Http\Requests\StoreCertificateRequest;
use App\Models\HalalCertificate;
use App\Models\Ingredient;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    private CertificateService $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $certificates = $this->certificateService->getAll($companyId, $request->only([
            'status', 'issuing_body', 'ingredient_id', 'expiring_within_days',
        ]));

        $summary = $this->certificateService->getStatusSummary($companyId);

        return Inertia::render('Certificates/Index', [
            'certificates' => $certificates,
            'summary' => $summary,
            'filters' => $request->only(['status', 'issuing_body', 'ingredient_id', 'expiring_within_days']),
        ]);
    }

    public function create(Request $request): Response
    {
        $companyId = $request->user()->activeCompanyId();

        $ingredients = Ingredient::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Certificates/Create', [
            'ingredients' => $ingredients,
            'preselectedIngredientId' => $request->query('ingredient_id'),
        ]);
    }

    public function store(StoreCertificateRequest $request)
    {
        $dto = HalalCertificateDTO::fromRequest(
            $request->validated(),
            $request->user()->activeCompanyId(),
            $request->user()->id
        );

        $certificate = $this->certificateService->create($dto);

        // Handle file upload if present
        if ($request->hasFile('document')) {
            $this->certificateService->uploadDocument($certificate, $request->file('document'));
        }

        return redirect()->route('certificates.index')
            ->with('success', 'Sertifikat halal berhasil ditambahkan.');
    }

    public function show(Request $request, HalalCertificate $certificate): Response
    {
        abort_if($certificate->company_id !== $request->user()->activeCompanyId(), 403);

        $certificate->load(['ingredient.supplier', 'uploader']);

        return Inertia::render('Certificates/Show', [
            'certificate' => $certificate,
        ]);
    }

    public function edit(Request $request, HalalCertificate $certificate): Response
    {
        abort_if($certificate->company_id !== $request->user()->activeCompanyId(), 403);

        $companyId = $request->user()->activeCompanyId();

        $ingredients = Ingredient::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return Inertia::render('Certificates/Edit', [
            'certificate' => $certificate,
            'ingredients' => $ingredients,
        ]);
    }

    public function update(StoreCertificateRequest $request, HalalCertificate $certificate)
    {
        abort_if($certificate->company_id !== $request->user()->activeCompanyId(), 403);

        $dto = HalalCertificateDTO::fromRequest(
            $request->validated(),
            $request->user()->activeCompanyId(),
            $request->user()->id
        );

        $this->certificateService->update($certificate, $dto);

        if ($request->hasFile('document')) {
            $this->certificateService->uploadDocument($certificate, $request->file('document'));
        }

        return redirect()->route('certificates.show', $certificate)
            ->with('success', 'Sertifikat berhasil diperbarui.');
    }

    public function destroy(Request $request, HalalCertificate $certificate)
    {
        abort_if($certificate->company_id !== $request->user()->activeCompanyId(), 403);

        $this->certificateService->delete($certificate);

        return redirect()->route('certificates.index')
            ->with('success', 'Sertifikat berhasil dihapus.');
    }

    public function uploadDocument(Request $request, HalalCertificate $certificate)
    {
        abort_if($certificate->company_id !== $request->user()->activeCompanyId(), 403);

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $this->certificateService->uploadDocument($certificate, $request->file('document'));

        return back()->with('success', 'Dokumen sertifikat berhasil diunggah.');
    }

    public function download(Request $request, HalalCertificate $certificate)
    {
        abort_if($certificate->company_id !== $request->user()->activeCompanyId(), 403);

        if (!$certificate->document_path || !Storage::disk('local')->exists($certificate->document_path)) {
            abort(404, 'File sertifikat tidak ditemukan.');
        }

        return response()->download(
            Storage::disk('local')->path($certificate->document_path),
            $certificate->original_filename ?? 'sertifikat.pdf'
        );
    }
}