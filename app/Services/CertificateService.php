<?php

namespace App\Services;

use App\DTOs\HalalCertificateDTO;
use App\Models\AuditLog;
use App\Models\HalalCertificate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CertificateService
{
    /**
     * Get all certificates for a company with optional filters.
     */
    public function getAll(int $companyId, array $filters = []): Collection
    {
        $query = HalalCertificate::where('company_id', $companyId)
            ->with(['ingredient', 'uploader']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['issuing_body'])) {
            $query->where('issuing_body', $filters['issuing_body']);
        }

        if (isset($filters['ingredient_id'])) {
            $query->where('ingredient_id', $filters['ingredient_id']);
        }

        if (isset($filters['expiring_within_days'])) {
            $query->expiringWithinDays((int) $filters['expiring_within_days']);
        }

        return $query->orderBy('expiry_date', 'asc')->get();
    }

    /**
     * Create a new certificate record.
     */
    public function create(HalalCertificateDTO $dto): HalalCertificate
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['status'] = $this->computeStatus($dto->expiry_date);

            $certificate = HalalCertificate::create($data);

            AuditLog::log($certificate, 'created', null, $certificate->toArray());

            return $certificate->load('ingredient');
        });
    }

    /**
     * Update an existing certificate.
     */
    public function update(HalalCertificate $certificate, HalalCertificateDTO $dto): HalalCertificate
    {
        return DB::transaction(function () use ($certificate, $dto) {
            $oldValues = $certificate->toArray();

            $data = $dto->toArray();
            $data['status'] = $this->computeStatus($dto->expiry_date);

            $certificate->update($data);

            AuditLog::log($certificate, 'updated', $oldValues, $certificate->fresh()->toArray());

            return $certificate->fresh(['ingredient']);
        });
    }

    /**
     * Upload a certificate document file.
     */
    public function uploadDocument(HalalCertificate $certificate, UploadedFile $file): HalalCertificate
    {
        $companyId = $certificate->company_id;
        $path = $file->store("certificates/company-{$companyId}", 'local');

        $oldPath = $certificate->document_path;

        $certificate->update([
            'document_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        // Clean up old file if it exists
        if ($oldPath && Storage::disk('local')->exists($oldPath)) {
            Storage::disk('local')->delete($oldPath);
        }

        AuditLog::log($certificate, 'uploaded', null, [
            'document_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
        ]);

        return $certificate;
    }

    /**
     * Delete a certificate.
     */
    public function delete(HalalCertificate $certificate): bool
    {
        return DB::transaction(function () use ($certificate) {
            AuditLog::log($certificate, 'deleted', $certificate->toArray());

            if ($certificate->document_path && Storage::disk('local')->exists($certificate->document_path)) {
                Storage::disk('local')->delete($certificate->document_path);
            }

            return $certificate->delete();
        });
    }

    /**
     * Refresh the cached status for all certificates in a company.
     * Returns a summary of changes.
     */
    public function refreshStatuses(int $companyId): array
    {
        $certificates = HalalCertificate::where('company_id', $companyId)->get();

        $changes = [
            'total' => $certificates->count(),
            'updated' => 0,
            'valid' => 0,
            'expiring_soon' => 0,
            'expired' => 0,
        ];

        foreach ($certificates as $certificate) {
            $newStatus = $this->computeStatus($certificate->expiry_date->toDateString());
            $changes[$newStatus]++;

            if ($certificate->status !== $newStatus) {
                $oldStatus = $certificate->status;
                $certificate->update(['status' => $newStatus]);
                $changes['updated']++;

                AuditLog::log($certificate, 'status_changed', [
                    'status' => $oldStatus,
                ], [
                    'status' => $newStatus,
                ]);
            }
        }

        return $changes;
    }

    /**
     * Get certificates expiring within X days.
     */
    public function getExpiringSoon(int $companyId, int $days = 90): Collection
    {
        return HalalCertificate::where('company_id', $companyId)
            ->expiringWithinDays($days)
            ->with(['ingredient.supplier'])
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Get a summary of certificate statuses for a company.
     */
    public function getStatusSummary(int $companyId): array
    {
        $certificates = HalalCertificate::where('company_id', $companyId)->get();

        return [
            'total' => $certificates->count(),
            'valid' => $certificates->where('status', 'valid')->count(),
            'expiring_soon' => $certificates->where('status', 'expiring_soon')->count(),
            'expired' => $certificates->where('status', 'expired')->count(),
        ];
    }

    // ----------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------

    /**
     * Compute the certificate status from its expiry date string.
     */
    private function computeStatus(string $expiryDate, int $thresholdDays = 90): string
    {
        $expiry = Carbon::parse($expiryDate);

        if ($expiry->isPast()) {
            return 'expired';
        }

        if ($expiry->isBefore(Carbon::now()->addDays($thresholdDays))) {
            return 'expiring_soon';
        }

        return 'valid';
    }
}