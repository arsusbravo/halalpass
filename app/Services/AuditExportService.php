<?php

namespace App\Services;

use App\DTOs\AuditExportDTO;
use App\Http\Controllers\SjphController;
use App\Models\Facility;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\SjphDocument;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class AuditExportService
{
    /**
     * Generate a ZIP export for SIHALAL submission.
     */
    public function generateExport(AuditExportDTO $dto): string
    {
        $companyId = $dto->company_id;

        // Load data
        $ingredients = Ingredient::where('company_id', $companyId)
            ->with(['supplier', 'halalCertificates'])
            ->orderBy('code')
            ->get();

        $productsQuery = Product::where('company_id', $companyId)->active()->with('ingredients');
        if (!empty($dto->product_ids)) {
            $productsQuery->whereIn('id', $dto->product_ids);
        }
        $products = $productsQuery->orderBy('code')->get();

        // Create temp dir
        $tempDir = storage_path('app/temp/export_' . uniqid());
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // 1. Daftar Bahan (Materials List)
        $this->generateDaftarBahan($ingredients, $tempDir);

        // 2. Matriks Bahan (Material Matrix)
        if ($dto->include_material_matrix) {
            $this->generateMatriksBahan($products, $ingredients, $tempDir);
        }

        // 3. Daftar Sertifikat Halal (Certificate List)
        $this->generateDaftarSertifikat($ingredients, $tempDir);

        // 4. SJPH Documents
        $this->generateSjphFiles($companyId, $tempDir);

        // 5. Certificate PDFs (from legacy certificates table if any)
        if ($dto->include_certificates) {
            $this->copyCertificateFiles($ingredients, $tempDir);
        }

        // Create ZIP
        $zipPath = storage_path('app/temp/export_' . date('Y-m-d_His') . '.zip');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $relativePath = substr($file->getRealPath(), strlen($tempDir) + 1);
            $zip->addFile($file->getRealPath(), $relativePath);
        }

        $zip->close();

        // Cleanup temp dir
        $this->deleteDirectory($tempDir);

        return $zipPath;
    }

    /**
     * 01_Daftar_Bahan.csv — Complete ingredient list with certificate info.
     */
    private function generateDaftarBahan($ingredients, string $tempDir): void
    {
        $fp = fopen($tempDir . '/01_Daftar_Bahan.csv', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for Excel

        fputcsv($fp, [
            'No', 'Kode Bahan', 'Nama Bahan', 'Jenis Bahan', 'Kategori',
            'Merek/Produsen', 'Tingkat Risiko', 'No Sertifikat Halal', 'Status Sertifikat',
        ]);

        $no = 1;
        foreach ($ingredients as $ingredient) {
            $typeLabel = match ($ingredient->type) {
                'composite' => 'Bahan Komposit',
                default => 'Bahan Sederhana',
            };

            $categoryLabel = match ($ingredient->category) {
                'bahan_baku' => 'Bahan Baku',
                'bahan_tambahan' => 'Bahan Tambahan',
                'bahan_penolong' => 'Bahan Penolong',
                default => $ingredient->category,
            };

            $riskLabel = match ($ingredient->halal_risk_level) {
                'no_risk' => 'Tanpa Risiko',
                'low_risk' => 'Risiko Rendah',
                'medium_risk' => 'Risiko Sedang',
                'high_risk' => 'Risiko Tinggi',
                default => '-',
            };

            // Get certificate: inline first, fallback to relationship
            $shNumber = $ingredient->sh_number;
            $certStatusLabel = $this->getCertStatusLabel($ingredient);

            // If no inline cert, check legacy certificates table
            if (!$shNumber && $ingredient->halalCertificates->isNotEmpty()) {
                $bestCert = $ingredient->halalCertificates->sortByDesc('created_at')->first();
                $shNumber = $bestCert->sh_number;
                $certStatusLabel = 'BERLAKU (Legacy)';
            }

            fputcsv($fp, [
                $no++,
                $ingredient->code ?? '-',
                $ingredient->name,
                $typeLabel,
                $categoryLabel,
                $ingredient->brand ?? '-',
                $riskLabel,
                $shNumber ?? '-',
                $certStatusLabel,
            ]);
        }

        fclose($fp);
    }

    private function generateSjphFiles(int $companyId, string $tempDir): void
    {
        $facilities = Facility::where('company_id', $companyId)->active()->get();
        $controller = app(SjphController::class);
    
        foreach ($facilities as $facility) {
            $document = SjphDocument::where('company_id', $companyId)
                ->where('facility_id', $facility->id)
                ->where('status', '!=', 'archived')
                ->latest()
                ->first();
    
            if (!$document) continue;
    
            $pdf = $controller->buildPdf($companyId, $facility);
    
            $filename = 'SJPH_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $facility->name) . '.pdf';
            file_put_contents($tempDir . '/' . $filename, $pdf->output());
        }
    }

    /**
     * 02_Matriks_Bahan.csv — Product × Ingredient matrix.
     */
    private function generateMatriksBahan($products, $ingredients, string $tempDir): void
    {
        $fp = fopen($tempDir . '/02_Matriks_Bahan.csv', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row: fixed columns + one column per product
        $header = ['No', 'Kode Bahan', 'Nama Bahan', 'No SH', 'Status SH'];
        foreach ($products as $product) {
            $header[] = $product->name . ' (' . ($product->code ?? '') . ')';
        }
        fputcsv($fp, $header);

        $no = 1;
        foreach ($ingredients as $ingredient) {
            $shNumber = $ingredient->sh_number;
            $statusLabel = $this->getCertStatusLabel($ingredient);

            if (!$shNumber && $ingredient->halalCertificates->isNotEmpty()) {
                $bestCert = $ingredient->halalCertificates->sortByDesc('created_at')->first();
                $shNumber = $bestCert->sh_number;
                $statusLabel = 'BERLAKU';
            }

            $row = [
                $no++,
                $ingredient->code ?? '-',
                $ingredient->name,
                $shNumber ?? '-',
                $statusLabel,
            ];

            // Add percentage per product
            foreach ($products as $product) {
                $pivot = $product->ingredients->firstWhere('id', $ingredient->id);
                if ($pivot && $pivot->pivot) {
                    $row[] = $pivot->pivot->percentage
                        ? number_format($pivot->pivot->percentage, 2) . '%'
                        : 'Ya';
                } else {
                    $row[] = '-';
                }
            }

            fputcsv($fp, $row);
        }

        fclose($fp);
    }

    /**
     * 03_Daftar_Sertifikat_Halal.csv — Certificate summary.
     */
    private function generateDaftarSertifikat($ingredients, string $tempDir): void
    {
        $fp = fopen($tempDir . '/03_Daftar_Sertifikat_Halal.csv', 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($fp, [
            'No', 'No Sertifikat Halal', 'Nama Bahan', 'Merek/Produsen',
            'Tingkat Risiko', 'Status', 'Keterangan',
        ]);

        $no = 1;

        foreach ($ingredients as $ingredient) {
            $riskLevel = $ingredient->halal_risk_level ?? 'medium_risk';

            // Skip no_risk ingredients
            if ($riskLevel === 'no_risk') {
                continue;
            }

            $riskLabel = match ($riskLevel) {
                'low_risk' => 'Risiko Rendah',
                'medium_risk' => 'Risiko Sedang',
                'high_risk' => 'Risiko Tinggi',
                default => '-',
            };

            if ($ingredient->sh_number) {
                fputcsv($fp, [
                    $no++,
                    $ingredient->sh_number,
                    $ingredient->name,
                    $ingredient->brand ?? '-',
                    $riskLabel,
                    'BERLAKU SEUMUR HIDUP',
                    'PP 42/2024',
                ]);
            } elseif ($ingredient->halalCertificates->isNotEmpty()) {
                // Legacy certificates
                foreach ($ingredient->halalCertificates as $cert) {
                    fputcsv($fp, [
                        $no++,
                        $cert->sh_number,
                        $ingredient->name,
                        $ingredient->brand ?? '-',
                        $riskLabel,
                        $cert->is_expired ? 'KADALUARSA' : 'BERLAKU',
                        $cert->issuing_body ?? '-',
                    ]);
                }
            } else {
                // Missing certificate
                fputcsv($fp, [
                    $no++,
                    'BELUM ADA',
                    $ingredient->name,
                    $ingredient->brand ?? '-',
                    $riskLabel,
                    'BELUM ADA SERTIFIKAT',
                    $riskLevel === 'low_risk' ? 'Opsional' : 'WAJIB',
                ]);
            }
        }

        fclose($fp);
    }

    /**
     * Copy certificate PDF files from legacy certificates table.
     */
    private function copyCertificateFiles($ingredients, string $tempDir): void
    {
        $certDir = $tempDir . '/sertifikat';
        $hasCerts = false;

        foreach ($ingredients as $ingredient) {
            foreach ($ingredient->halalCertificates as $cert) {
                if ($cert->document_path && Storage::disk('local')->exists($cert->document_path)) {
                    if (!$hasCerts) {
                        mkdir($certDir, 0755, true);
                        $hasCerts = true;
                    }
                    $filename = $cert->sh_number . '_' . $ingredient->name . '.' .
                        pathinfo($cert->document_path, PATHINFO_EXTENSION);
                    $filename = preg_replace('/[^a-zA-Z0-9_\-.]/', '_', $filename);
                    copy(Storage::disk('local')->path($cert->document_path), $certDir . '/' . $filename);
                }
            }
        }
    }

    /**
     * Get human-readable certificate status for an ingredient.
     */
    private function getCertStatusLabel(Ingredient $ingredient): string
    {
        $riskLevel = $ingredient->halal_risk_level ?? 'medium_risk';

        if ($riskLevel === 'no_risk') {
            return 'TIDAK DIPERLUKAN';
        }

        if ($ingredient->sh_number) {
            return 'BERLAKU SEUMUR HIDUP';
        }

        if ($riskLevel === 'low_risk') {
            return 'OPSIONAL - BELUM ADA';
        }

        return 'BELUM ADA SERTIFIKAT';
    }

    /**
     * Recursively delete a directory.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }

        rmdir($dir);
    }
}