<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Mocked integration with BPJPH SIHALAL system.
 *
 * Interface-driven: implement SihalalApiInterface when the real API becomes available.
 * Current implementation logs all calls and returns simulated responses.
 */
class SihalalApiService
{
    /**
     * Submit a product registration to SIHALAL.
     *
     * @return array{success: bool, reference_number: string|null, message: string}
     */
    public function submitProductRegistration(int $companyId, array $productData): array
    {
        Log::info('SIHALAL Mock: Product registration submitted', [
            'company_id' => $companyId,
            'product' => $productData,
        ]);

        // Simulated response
        return [
            'success' => true,
            'reference_number' => 'SHL-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'message' => 'Permohonan pendaftaran produk berhasil diterima.',
            'status' => 'pending_review',
            'estimated_review_days' => 14,
        ];
    }

    /**
     * Upload materials list (Daftar Bahan) to SIHALAL.
     *
     * @return array{success: bool, upload_id: string|null, message: string}
     */
    public function uploadBahanList(int $companyId, string $filePath): array
    {
        Log::info('SIHALAL Mock: Bahan list uploaded', [
            'company_id' => $companyId,
            'file_path' => $filePath,
        ]);

        return [
            'success' => true,
            'upload_id' => 'UPL-' . uniqid(),
            'message' => 'Daftar bahan berhasil diunggah ke SIHALAL.',
            'validation_status' => 'valid',
            'warnings' => [],
        ];
    }

    /**
     * Upload material matrix (Matriks Bahan) to SIHALAL.
     *
     * @return array{success: bool, upload_id: string|null, message: string}
     */
    public function uploadMatriksBahan(int $companyId, string $filePath): array
    {
        Log::info('SIHALAL Mock: Matriks Bahan uploaded', [
            'company_id' => $companyId,
            'file_path' => $filePath,
        ]);

        return [
            'success' => true,
            'upload_id' => 'UPL-' . uniqid(),
            'message' => 'Matriks bahan berhasil diunggah ke SIHALAL.',
            'validation_status' => 'valid',
            'warnings' => [],
        ];
    }

    /**
     * Check the status of a submitted application.
     *
     * @return array{status: string, message: string, details: array}
     */
    public function checkApplicationStatus(string $referenceNumber): array
    {
        Log::info('SIHALAL Mock: Status check', [
            'reference_number' => $referenceNumber,
        ]);

        // Simulated — randomly return different statuses for demo
        $statuses = [
            ['status' => 'pending_review', 'message' => 'Permohonan sedang dalam antrian review.'],
            ['status' => 'under_review', 'message' => 'Permohonan sedang diperiksa oleh auditor.'],
            ['status' => 'revision_needed', 'message' => 'Diperlukan revisi dokumen.'],
            ['status' => 'approved', 'message' => 'Permohonan disetujui. Sertifikat sedang diterbitkan.'],
        ];

        $result = $statuses[array_rand($statuses)];
        $result['reference_number'] = $referenceNumber;
        $result['details'] = [
            'submitted_at' => now()->subDays(rand(1, 30))->toDateTimeString(),
            'last_updated' => now()->toDateTimeString(),
        ];

        return $result;
    }

    /**
     * Validate a halal certificate number against SIHALAL database.
     *
     * @return array{valid: bool, certificate: array|null, message: string}
     */
    public function validateCertificate(string $shNumber): array
    {
        Log::info('SIHALAL Mock: Certificate validation', [
            'sh_number' => $shNumber,
        ]);

        // Simulate: certificates starting with "LPPOM" are valid
        $isValid = str_starts_with($shNumber, 'LPPOM') || str_starts_with($shNumber, 'LPH');

        return [
            'valid' => $isValid,
            'certificate' => $isValid ? [
                'sh_number' => $shNumber,
                'status' => 'active',
                'issuing_body' => str_starts_with($shNumber, 'LPPOM') ? 'MUI' : 'LPH',
            ] : null,
            'message' => $isValid
                ? 'Sertifikat terverifikasi di database SIHALAL.'
                : 'Sertifikat tidak ditemukan di database SIHALAL.',
        ];
    }
}