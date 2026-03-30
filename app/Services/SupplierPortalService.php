<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HalalCertificate;
use App\Models\Ingredient;
use App\Models\SupplierAccessToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SupplierPortalService
{
    /**
     * Generate an access token for a supplier to upload a certificate.
     */
    public function generateAccessToken(
        int $companyId,
        int $supplierId,
        ?int $ingredientId = null,
        int $validDays = 30
    ): SupplierAccessToken {
        return SupplierAccessToken::generateToken(
            $companyId,
            $supplierId,
            $ingredientId,
            $validDays
        );
    }

    /**
     * Validate an access token and return the token model if valid.
     */
    public function validateToken(string $token): ?SupplierAccessToken
    {
        $accessToken = SupplierAccessToken::where('token', $token)
            ->with(['supplier', 'ingredient', 'company'])
            ->first();

        if (!$accessToken || !$accessToken->is_valid) {
            return null;
        }

        return $accessToken;
    }

    /**
     * Get the portal view data for a valid token.
     * Returns only what the supplier needs to see — no internal data.
     */
    public function getPortalData(SupplierAccessToken $token): array
    {
        return [
            'supplier_name' => $token->supplier->name,
            'company_name' => $token->company->name,
            'ingredient' => $token->ingredient ? [
                'id' => $token->ingredient->id,
                'name' => $token->ingredient->name,
                'code' => $token->ingredient->code,
            ] : null,
            'ingredients_requiring_certs' => $this->getIngredientsNeedingCerts($token),
            'expires_at' => $token->expires_at->toDateTimeString(),
        ];
    }

    /**
     * Handle a certificate upload from the supplier portal.
     */
    public function uploadCertificate(
        SupplierAccessToken $token,
        int $ingredientId,
        array $certificateData,
        UploadedFile $file
    ): HalalCertificate {
        return DB::transaction(function () use ($token, $ingredientId, $certificateData, $file) {
            $companyId = $token->company_id;

            // Store the file
            $path = $file->store("certificates/company-{$companyId}/supplier-uploads", 'local');

            $certificate = HalalCertificate::create([
                'company_id' => $companyId,
                'ingredient_id' => $ingredientId,
                'sh_number' => $certificateData['sh_number'],
                'issuing_body' => $certificateData['issuing_body'],
                'issuing_body_name' => $certificateData['issuing_body_name'] ?? null,
                'issue_date' => $certificateData['issue_date'] ?? null,
                'expiry_date' => $certificateData['expiry_date'],
                'document_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'status' => 'valid',
                'notes' => 'Uploaded via supplier portal by ' . $token->supplier->name,
                'uploaded_by' => null, // No user — supplier uploaded
            ]);

            // Mark token as used
            $token->update([
                'used_at' => Carbon::now(),
                'ip_address' => request()->ip(),
            ]);

            AuditLog::log($certificate, 'uploaded', null, [
                'supplier_id' => $token->supplier_id,
                'supplier_name' => $token->supplier->name,
                'uploaded_via' => 'supplier_portal',
            ]);

            return $certificate;
        });
    }

    /**
     * Get all active tokens for a company.
     */
    public function getActiveTokens(int $companyId): \Illuminate\Database\Eloquent\Collection
    {
        return SupplierAccessToken::where('company_id', $companyId)
            ->valid()
            ->with(['supplier', 'ingredient'])
            ->orderBy('expires_at', 'asc')
            ->get();
    }

    /**
     * Revoke (delete) an access token.
     */
    public function revokeToken(SupplierAccessToken $token): bool
    {
        return $token->delete();
    }

    // ----------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------

    /**
     * Get ingredients from this supplier that need certificates.
     */
    private function getIngredientsNeedingCerts(SupplierAccessToken $token): array
    {
        // If token is for a specific ingredient, only show that one
        if ($token->ingredient_id) {
            $ingredient = $token->ingredient;
            return [[
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'code' => $ingredient->code,
                'current_cert_status' => $this->getIngredientCertStatus($ingredient),
            ]];
        }

        // Otherwise, show all ingredients from this supplier that need attention
        $ingredients = Ingredient::where('company_id', $token->company_id)
            ->where('supplier_id', $token->supplier_id)
            ->with('halalCertificates')
            ->get();

        return $ingredients->map(function ($ingredient) {
            return [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'code' => $ingredient->code,
                'current_cert_status' => $this->getIngredientCertStatus($ingredient),
            ];
        })->toArray();
    }

    private function getIngredientCertStatus(Ingredient $ingredient): string
    {
        $latestCert = $ingredient->halalCertificates
            ->sortByDesc('expiry_date')
            ->first();

        if (!$latestCert) {
            return 'missing';
        }

        return $latestCert->computed_status;
    }
}