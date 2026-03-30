<?php

namespace App\Services;

use App\DTOs\AuditExportDTO;
use App\Models\AuditLog;
use App\Models\HalalCertificate;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class AuditExportService
{
    private IngredientService $ingredientService;

    public function __construct(IngredientService $ingredientService)
    {
        $this->ingredientService = $ingredientService;
    }

    /**
     * Generate the full audit-ready export as a ZIP file.
     * Returns the path to the ZIP file in storage.
     */
    public function generateExport(AuditExportDTO $dto): string
    {
        $timestamp = Carbon::now()->format('Ymd_His');
        $zipFilename = "audit_export_{$dto->company_id}_{$timestamp}.zip";
        $zipPath = storage_path("app/exports/{$zipFilename}");

        // Ensure the directory exists
        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Could not create ZIP file at {$zipPath}");
        }

        // 1. Daftar Bahan (Materials List)
        $bahanCsv = $this->generateBahanList($dto);
        $zip->addFromString('01_Daftar_Bahan.csv', $bahanCsv);

        // 2. Matriks Bahan (Material Matrix)
        if ($dto->include_material_matrix) {
            $matriksCsv = $this->generateMatriksBahan($dto);
            $zip->addFromString('02_Matriks_Bahan.csv', $matriksCsv);
        }

        // 3. Certificate summary
        if ($dto->include_certificates) {
            $certCsv = $this->generateCertificateSummary($dto);
            $zip->addFromString('03_Daftar_Sertifikat_Halal.csv', $certCsv);
        }

        // 4. Copy actual certificate PDFs
        if ($dto->include_certificates) {
            $this->addCertificateFiles($zip, $dto);
        }

        $zip->close();

        // Log the export
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'company_id' => $dto->company_id,
                'user_id' => $user->id,
                'auditable_type' => 'App\\Models\\Company',
                'auditable_id' => $dto->company_id,
                'action' => 'exported',
                'new_values' => [
                    'type' => 'audit_export',
                    'filename' => $zipFilename,
                    'product_ids' => $dto->product_ids,
                    'facility_ids' => $dto->facility_ids,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return $zipPath;
    }

    /**
     * Generate the Daftar Bahan (Materials List) CSV.
     * Format follows SIHALAL requirements.
     */
    private function generateBahanList(AuditExportDTO $dto): string
    {
        $ingredients = $this->getFilteredIngredients($dto);

        $rows = [];

        // BOM for Excel UTF-8 compatibility
        $rows[] = "\xEF\xBB\xBF" . implode(',', [
            'No',
            'Kode Bahan',
            'Nama Bahan',
            'Jenis Bahan',
            'Kategori',
            'Merek',
            'Produsen',
            'Negara Asal',
            'Pemasok',
            'No Sertifikat Halal',
            'Lembaga Penerbit',
            'Tanggal Kadaluarsa SH',
            'Status SH',
        ]);

        $no = 1;
        foreach ($ingredients as $ingredient) {
            $cert = $ingredient->halalCertificates->sortByDesc('expiry_date')->first();

            $rows[] = implode(',', [
                $no++,
                $this->csvEscape($ingredient->code),
                $this->csvEscape($ingredient->name),
                $ingredient->type === 'simple' ? 'Bahan Sederhana' : 'Bahan Komposit',
                $this->categoryLabel($ingredient->category),
                $this->csvEscape($ingredient->brand ?? '-'),
                $this->csvEscape($ingredient->manufacturer ?? '-'),
                $ingredient->origin_country ?? '-',
                $this->csvEscape($ingredient->supplier?->name ?? '-'),
                $this->csvEscape($cert?->sh_number ?? 'BELUM ADA'),
                $this->csvEscape($cert?->issuing_body_name ?? $cert?->issuing_body ?? '-'),
                $cert?->expiry_date?->format('d/m/Y') ?? '-',
                $cert ? $this->statusLabel($cert->status) : 'BELUM ADA SERTIFIKAT',
            ]);
        }

        return implode("\n", $rows);
    }

    /**
     * Generate the Matriks Bahan (Material Matrix) CSV.
     * Cross-reference: products × ingredients with cert status.
     */
    private function generateMatriksBahan(AuditExportDTO $dto): string
    {
        $products = $this->getFilteredProducts($dto);
        $allIngredients = $this->getFilteredIngredients($dto);

        $rows = [];

        // Header row: first columns + one column per product
        $header = ['No', 'Kode Bahan', 'Nama Bahan', 'No SH', 'Status SH'];
        foreach ($products as $product) {
            $header[] = $this->csvEscape($product->name . ' (' . $product->code . ')');
        }

        $rows[] = "\xEF\xBB\xBF" . implode(',', $header);

        // Data rows
        $no = 1;
        foreach ($allIngredients as $ingredient) {
            $cert = $ingredient->halalCertificates->sortByDesc('expiry_date')->first();

            $row = [
                $no++,
                $this->csvEscape($ingredient->code),
                $this->csvEscape($ingredient->name),
                $this->csvEscape($cert?->sh_number ?? 'BELUM ADA'),
                $cert ? $this->statusLabel($cert->status) : 'TIDAK ADA',
            ];

            // For each product, mark if this ingredient is used
            foreach ($products as $product) {
                $pivot = $product->ingredients->firstWhere('id', $ingredient->id);

                if ($pivot) {
                    $percentage = $pivot->pivot->percentage
                        ? number_format($pivot->pivot->percentage, 2) . '%'
                        : 'Ya';
                    $critical = $pivot->pivot->is_critical ? ' [KRITIS]' : '';
                    $row[] = $this->csvEscape($percentage . $critical);
                } else {
                    $row[] = '-';
                }
            }

            $rows[] = implode(',', $row);
        }

        return implode("\n", $rows);
    }

    /**
     * Generate a certificate summary CSV.
     */
    private function generateCertificateSummary(AuditExportDTO $dto): string
    {
        $certificates = HalalCertificate::where('company_id', $dto->company_id)
            ->with(['ingredient.supplier'])
            ->orderBy('expiry_date', 'asc')
            ->get();

        $rows = [];

        $rows[] = "\xEF\xBB\xBF" . implode(',', [
            'No',
            'No Sertifikat Halal',
            'Nama Bahan',
            'Lembaga Penerbit',
            'Nama Lembaga',
            'Tanggal Terbit',
            'Tanggal Kadaluarsa',
            'Sisa Hari',
            'Status',
            'File Tersedia',
        ]);

        $no = 1;
        foreach ($certificates as $cert) {
            $rows[] = implode(',', [
                $no++,
                $this->csvEscape($cert->sh_number),
                $this->csvEscape($cert->ingredient?->name ?? '-'),
                $cert->issuing_body,
                $this->csvEscape($cert->issuing_body_name ?? '-'),
                $cert->issue_date?->format('d/m/Y') ?? '-',
                $cert->expiry_date->format('d/m/Y'),
                $cert->days_until_expiry,
                $this->statusLabel($cert->status),
                $cert->document_path ? 'Ya' : 'Tidak',
            ]);
        }

        return implode("\n", $rows);
    }

    /**
     * Add actual certificate PDF files to the ZIP.
     */
    private function addCertificateFiles(ZipArchive $zip, AuditExportDTO $dto): void
    {
        $certificates = HalalCertificate::where('company_id', $dto->company_id)
            ->whereNotNull('document_path')
            ->with('ingredient')
            ->get();

        $zip->addEmptyDir('sertifikat');

        foreach ($certificates as $cert) {
            if ($cert->document_path && Storage::disk('local')->exists($cert->document_path)) {
                $filename = $cert->ingredient
                    ? str_replace(' ', '_', $cert->ingredient->name) . '_' . $cert->sh_number
                    : $cert->sh_number;

                $extension = pathinfo($cert->original_filename ?? $cert->document_path, PATHINFO_EXTENSION) ?: 'pdf';

                $zip->addFile(
                    Storage::disk('local')->path($cert->document_path),
                    "sertifikat/{$filename}.{$extension}"
                );
            }
        }
    }

    // ----------------------------------------------------------------
    //  Query helpers
    // ----------------------------------------------------------------

    private function getFilteredProducts(AuditExportDTO $dto)
    {
        $query = Product::where('company_id', $dto->company_id)
            ->with('ingredients.halalCertificates');

        if ($dto->product_ids) {
            $query->whereIn('id', $dto->product_ids);
        }

        if ($dto->facility_ids) {
            $query->whereIn('facility_id', $dto->facility_ids);
        }

        return $query->orderBy('name')->get();
    }

    private function getFilteredIngredients(AuditExportDTO $dto)
    {
        $query = Ingredient::where('company_id', $dto->company_id)
            ->with(['halalCertificates', 'supplier']);

        if ($dto->product_ids) {
            $productIngredientIds = Product::whereIn('id', $dto->product_ids)
                ->with('ingredients')
                ->get()
                ->pluck('ingredients')
                ->flatten()
                ->pluck('id')
                ->unique();

            $query->whereIn('id', $productIngredientIds);
        }

        return $query->orderBy('code')->get();
    }

    // ----------------------------------------------------------------
    //  Formatting helpers
    // ----------------------------------------------------------------

    private function csvEscape(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        // Wrap in quotes if the value contains commas, quotes, or newlines
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }

    private function categoryLabel(string $category): string
    {
        return match ($category) {
            'bahan_baku' => 'Bahan Baku',
            'bahan_tambahan' => 'Bahan Tambahan',
            'bahan_penolong' => 'Bahan Penolong',
            default => $category,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'valid' => 'Berlaku',
            'expiring_soon' => 'Segera Kadaluarsa',
            'expired' => 'KADALUARSA',
            'missing' => 'TIDAK ADA',
            default => $status,
        };
    }
}