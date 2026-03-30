<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SjphDocument;
use Illuminate\Support\Facades\DB;

class SjphGeneratorService
{
    /**
     * Get or create a draft SJPH document for a facility.
     */
    public function getOrCreateDraft(int $companyId, int $facilityId): SjphDocument
    {
        $document = SjphDocument::where('company_id', $companyId)
            ->where('facility_id', $facilityId)
            ->where('status', 'draft')
            ->first();

        if (!$document) {
            $document = SjphDocument::create([
                'company_id' => $companyId,
                'facility_id' => $facilityId,
                'version' => '1.0',
                'status' => 'draft',
            ]);

            AuditLog::log($document, 'created', null, ['status' => 'draft']);
        }

        return $document->load('facility');
    }

    /**
     * Save a single SJPH criterion section.
     */
    public function saveSection(SjphDocument $document, string $criterion, array $data): SjphDocument
    {
        if (!in_array($criterion, SjphDocument::CRITERIA)) {
            throw new \InvalidArgumentException("Invalid SJPH criterion: {$criterion}");
        }

        $oldValue = $document->{$criterion};

        $document->update([$criterion => $data]);

        AuditLog::log($document, 'updated', [
            $criterion => $oldValue,
        ], [
            $criterion => $data,
        ]);

        return $document->fresh();
    }

    /**
     * Get the current progress of a document.
     */
    public function getProgress(SjphDocument $document): array
    {
        $sections = [];

        foreach (SjphDocument::CRITERIA as $criterion) {
            $sections[] = [
                'key' => $criterion,
                'label' => SjphDocument::CRITERIA_LABELS[$criterion],
                'filled' => !empty($document->{$criterion}),
                'data' => $document->{$criterion},
            ];
        }

        return [
            'document_id' => $document->id,
            'version' => $document->version,
            'status' => $document->status,
            'completion_percentage' => $document->completion_percentage,
            'incomplete_criteria' => $document->incomplete_criteria,
            'sections' => $sections,
        ];
    }

    /**
     * Submit a document for review (status: draft → in_review).
     */
    public function submitForReview(SjphDocument $document): SjphDocument
    {
        if ($document->status !== 'draft') {
            throw new \LogicException("Only draft documents can be submitted for review.");
        }

        if ($document->completion_percentage < 100) {
            throw new \LogicException(
                "Cannot submit incomplete document ({$document->completion_percentage}% complete). "
                . "Missing: " . implode(', ', array_map(
                    fn ($k) => SjphDocument::CRITERIA_LABELS[$k],
                    $document->incomplete_criteria
                ))
            );
        }

        return $this->updateStatus($document, 'in_review');
    }

    /**
     * Approve a document (status: in_review → approved).
     */
    public function approve(SjphDocument $document, int $approvedByUserId): SjphDocument
    {
        if ($document->status !== 'in_review') {
            throw new \LogicException("Only documents in review can be approved.");
        }

        return DB::transaction(function () use ($document, $approvedByUserId) {
            $document->update([
                'status' => 'approved',
                'approved_by' => $approvedByUserId,
                'approved_at' => now(),
            ]);

            AuditLog::log($document, 'status_changed', [
                'status' => 'in_review',
            ], [
                'status' => 'approved',
                'approved_by' => $approvedByUserId,
            ]);

            return $document->fresh();
        });
    }

    /**
     * Reject back to draft (status: in_review → draft).
     */
    public function rejectToDraft(SjphDocument $document): SjphDocument
    {
        if ($document->status !== 'in_review') {
            throw new \LogicException("Only documents in review can be rejected.");
        }

        return $this->updateStatus($document, 'draft');
    }

    /**
     * Archive a document.
     */
    public function archive(SjphDocument $document): SjphDocument
    {
        return $this->updateStatus($document, 'archived');
    }

    /**
     * Create a new version from an approved document.
     */
    public function createNewVersion(SjphDocument $document): SjphDocument
    {
        if ($document->status !== 'approved') {
            throw new \LogicException("Can only create new versions from approved documents.");
        }

        $versionParts = explode('.', $document->version);
        $newVersion = $versionParts[0] . '.' . ((int) ($versionParts[1] ?? 0) + 1);

        $newDocument = $document->replicate();
        $newDocument->version = $newVersion;
        $newDocument->status = 'draft';
        $newDocument->approved_by = null;
        $newDocument->approved_at = null;
        $newDocument->document_path = null;
        $newDocument->save();

        // Archive the old version
        $this->archive($document);

        AuditLog::log($newDocument, 'created', null, [
            'version' => $newVersion,
            'based_on' => $document->id,
        ]);

        return $newDocument;
    }

    // ----------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------

    private function updateStatus(SjphDocument $document, string $newStatus): SjphDocument
    {
        $oldStatus = $document->status;

        $document->update(['status' => $newStatus]);

        AuditLog::log($document, 'status_changed', [
            'status' => $oldStatus,
        ], [
            'status' => $newStatus,
        ]);

        return $document->fresh();
    }
}