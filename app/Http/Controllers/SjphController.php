<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSjphSectionRequest;
use App\Models\Facility;
use App\Models\SjphDocument;
use App\Services\SjphGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SjphController extends Controller
{
    private SjphGeneratorService $sjphService;

    public function __construct(SjphGeneratorService $sjphService)
    {
        $this->sjphService = $sjphService;
    }

    public function show(Request $request, Facility $facility): Response
    {
        abort_if($facility->company_id !== $request->user()->activeCompanyId(), 403);

        $document = $this->sjphService->getOrCreateDraft(
            $request->user()->activeCompanyId(),
            $facility->id
        );

        $progress = $this->sjphService->getProgress($document);

        return Inertia::render('Sjph/Show', [
            'facility' => $facility,
            'document' => $document,
            'progress' => $progress,
            'criteriaLabels' => SjphDocument::CRITERIA_LABELS,
        ]);
    }

    public function saveSection(SaveSjphSectionRequest $request, SjphDocument $sjphDocument)
    {
        abort_if($sjphDocument->company_id !== $request->user()->activeCompanyId(), 403);

        if ($sjphDocument->status !== 'draft') {
            return back()->with('error', 'Hanya dokumen draft yang dapat diedit.');
        }

        $this->sjphService->saveSection(
            $sjphDocument,
            $request->validated()['criterion'],
            $request->validated()['data']
        );

        return back()->with('success', 'Bagian SJPH berhasil disimpan.');
    }

    public function submit(Request $request, SjphDocument $sjphDocument)
    {
        abort_if($sjphDocument->company_id !== $request->user()->activeCompanyId(), 403);

        try {
            $this->sjphService->submitForReview($sjphDocument);

            return back()->with('success', 'Dokumen SJPH berhasil diajukan untuk review.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, SjphDocument $sjphDocument)
    {
        abort_if($sjphDocument->company_id !== $request->user()->activeCompanyId(), 403);

        $user = $request->user();

        if (!in_array($user->role, ['owner', 'admin'])) {
            return back()->with('error', 'Hanya owner atau admin yang dapat menyetujui.');
        }

        try {
            $this->sjphService->approve($sjphDocument, $user->id);

            return back()->with('success', 'Dokumen SJPH berhasil disetujui.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, SjphDocument $sjphDocument)
    {
        abort_if($sjphDocument->company_id !== $request->user()->activeCompanyId(), 403);

        try {
            $this->sjphService->rejectToDraft($sjphDocument);

            return back()->with('success', 'Dokumen dikembalikan ke draft.');
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function newVersion(Request $request, SjphDocument $sjphDocument)
    {
        abort_if($sjphDocument->company_id !== $request->user()->activeCompanyId(), 403);

        try {
            $newDoc = $this->sjphService->createNewVersion($sjphDocument);

            return redirect()->route('sjph.show', $newDoc->facility_id)
                ->with('success', "Versi baru ({$newDoc->version}) berhasil dibuat.");
        } catch (\LogicException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}