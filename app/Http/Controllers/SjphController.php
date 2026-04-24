<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Facility;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\SjphDocument;
use Illuminate\Http\Request;
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

        // Parse saved team members and trainings from the document
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

        // Stats for the page
        $ingredients = Ingredient::where('company_id', $companyId)->count();
        $ingredientsWithCert = Ingredient::where('company_id', $companyId)->whereNotNull('sh_number')->where('sh_number', '!=', '')->count();
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
            'team_members.*.name' => 'required|string|max:255',
            'team_members.*.position' => 'required|string|max:255',
            'team_members.*.role' => 'required|string|max:255',
            'trainings' => 'nullable|array',
            'trainings.*.date' => 'required|string|max:20',
            'trainings.*.topic' => 'required|string|max:255',
            'trainings.*.provider' => 'required|string|max:255',
            'trainings.*.attendees' => 'required|string|max:255',
        ]);

        $document = SjphDocument::firstOrCreate(
            [
                'company_id' => $companyId,
                'facility_id' => $facility->id,
                'status' => 'draft',
            ],
            ['version' => 1]
        );

        // If document was already approved/in_review, update it anyway
        if ($document->status !== 'draft') {
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

        $company = Company::findOrFail($companyId);
        $facilityAddress = implode(', ', array_filter([$facility->address, $facility->city, $facility->province]));

        $ingredients = Ingredient::where('company_id', $companyId)->orderBy('name')->get();
        $products = Product::where('company_id', $companyId)->active()->with('ingredients')->orderBy('name')->get();

        // Get team members and trainings
        $document = SjphDocument::where('company_id', $companyId)
            ->where('facility_id', $facility->id)
            ->where('status', '!=', 'archived')
            ->latest()
            ->first();

        $teamMembers = [];
        $trainings = [];
        if ($document) {
            $raw = json_decode($document->tim_manajemen_halal ?? '{}', true);
            $teamMembers = $raw['members'] ?? [];
            $raw = json_decode($document->pelatihan_edukasi ?? '{}', true);
            $trainings = $raw['trainings'] ?? [];
        }

        // Mark as approved (generating = ready)
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

        // Build the document
        $content = $this->buildDocument($company, $facility, $facilityAddress, $ingredients, $products, $teamMembers, $trainings);

        $filename = 'SJPH_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $facility->name) . '_' . now()->format('Y-m-d') . '.html';

        return response($content)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    private function buildDocument($company, $facility, $facilityAddress, $ingredients, $products, $teamMembers, $trainings): string
    {
        $companyName = e($company->name);
        $facilityName = e($facility->name);
        $facilityCode = e($facility->code ?? '-');
        $facilityAddr = e($facilityAddress);
        $capacity = e($facility->production_capacity ?? '-');
        $pic = e($facility->pic_name ?? '-');
        $today = now()->format('d/m/Y');
        $npwp = e($company->npwp ?? '________________');

        // Team table
        $teamRows = '';
        if (count($teamMembers) > 0) {
            $no = 1;
            foreach ($teamMembers as $m) {
                $teamRows .= "<tr><td>{$no}</td><td>" . e($m['name']) . "</td><td>" . e($m['position']) . "</td><td>" . e($m['role']) . "</td></tr>";
                $no++;
            }
        } else {
            $teamRows = '<tr><td colspan="4" style="text-align:center;color:#999;">Belum diisi — isi di halaman SJPH</td></tr>';
        }

        // Training table
        $trainingRows = '';
        if (count($trainings) > 0) {
            $no = 1;
            foreach ($trainings as $tr) {
                $trainingRows .= "<tr><td>{$no}</td><td>" . e($tr['date']) . "</td><td>" . e($tr['topic']) . "</td><td>" . e($tr['provider']) . "</td><td>" . e($tr['attendees']) . "</td></tr>";
                $no++;
            }
        } else {
            $trainingRows = '<tr><td colspan="5" style="text-align:center;color:#999;">Belum diisi — isi di halaman SJPH</td></tr>';
        }

        // Ingredient table
        $ingredientRows = '';
        $no = 1;
        foreach ($ingredients as $ing) {
            $risk = match ($ing->halal_risk_level) {
                'no_risk' => 'Tanpa Risiko', 'low_risk' => 'Risiko Rendah',
                'medium_risk' => 'Risiko Sedang', 'high_risk' => 'Risiko Tinggi', default => '-',
            };
            $sh = e($ing->sh_number ?? '-');
            $status = $ing->sh_number ? '✓ Bersertifikat' : ($ing->halal_risk_level === 'no_risk' ? 'Tidak Diperlukan' : '✗ Belum Ada');
            $brand = e($ing->brand ?? '-');
            $ingredientRows .= "<tr><td>{$no}</td><td>" . e($ing->name) . "</td><td>{$brand}</td><td>{$risk}</td><td style=\"font-family:monospace\">{$sh}</td><td>{$status}</td></tr>";
            $no++;
        }

        // Product table
        $productRows = '';
        $no = 1;
        foreach ($products as $prod) {
            $status = match ($prod->halal_status) {
                'compliant' => '✓ Sesuai', 'at_risk' => '⚠ Berisiko', 'non_compliant' => '✗ Tidak Sesuai', default => 'Pending',
            };
            $ings = $prod->ingredients->pluck('name')->implode(', ');
            $productRows .= "<tr><td>{$no}</td><td>" . e($prod->name) . "</td><td>" . e($prod->code ?? '-') . "</td><td>{$prod->halal_health_score}/100</td><td>{$status}</td><td>" . e($ings) . "</td></tr>";
            $no++;
        }

        $totalIng = $ingredients->count();
        $certIng = $ingredients->filter(fn($i) => !empty($i->sh_number))->count();
        $noRiskIng = $ingredients->where('halal_risk_level', 'no_risk')->count();
        $missingIng = $totalIng - $certIng - $noRiskIng;

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>SJPH — {$facilityName}</title>
<style>
    body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; max-width: 210mm; margin: 0 auto; padding: 20mm; color: #222; }
    h1 { text-align: center; font-size: 18pt; margin-bottom: 4px; }
    h2 { font-size: 14pt; border-bottom: 2px solid #166534; padding-bottom: 4px; margin-top: 30px; color: #166534; }
    h3 { font-size: 12pt; margin-top: 20px; }
    .subtitle { text-align: center; font-size: 11pt; color: #555; margin-bottom: 20px; }
    .meta { text-align: center; font-size: 10pt; color: #777; margin-bottom: 30px; }
    table { width: 100%; border-collapse: collapse; margin: 12px 0 20px; font-size: 10pt; }
    th { background: #166534; color: #fff; padding: 8px 10px; text-align: left; }
    td { padding: 6px 10px; border-bottom: 1px solid #ddd; }
    tr:hover td { background: #f9f9f9; }
    .sig-block { margin-top: 40px; }
    .sig-line { width: 200px; border-bottom: 1px solid #333; margin-top: 60px; }
    .policy-box { border: 1px solid #ddd; padding: 20px; margin: 15px 0; background: #fafafa; }
    .stat { display: inline-block; margin-right: 20px; }
    .stat b { color: #166534; }
    ol li { margin-bottom: 8px; }
    @media print { body { padding: 15mm; } }
</style>
</head>
<body>

<h1>SISTEM JAMINAN PRODUK HALAL (SJPH)</h1>
<p class="subtitle">{$companyName}</p>
<p class="meta">Fasilitas: {$facilityName} ({$facilityCode})<br>Alamat: {$facilityAddr}<br>NPWP: {$npwp}<br>Tanggal: {$today}</p>

<hr>

<!-- 1. KEBIJAKAN HALAL -->
<h2>1. Kebijakan Halal</h2>
<div class="policy-box">
<p><strong>{$companyName}</strong> berkomitmen untuk:</p>
<ol>
<li>Memproduksi, menyimpan, dan mendistribusikan produk yang memenuhi persyaratan halal sesuai dengan syariat Islam dan peraturan perundang-undangan yang berlaku.</li>
<li>Menjamin bahwa seluruh bahan yang digunakan dalam proses produksi telah memiliki status halal yang valid dan terdokumentasi.</li>
<li>Memastikan fasilitas produksi, peralatan, dan proses produksi terbebas dari kontaminasi bahan non-halal.</li>
<li>Melakukan perbaikan berkelanjutan terhadap Sistem Jaminan Produk Halal (SJPH) perusahaan.</li>
<li>Mematuhi seluruh regulasi terkait Jaminan Produk Halal sebagaimana diatur dalam UU No. 33/2014 dan PP No. 42/2024.</li>
</ol>
<p>Kebijakan ini berlaku untuk seluruh karyawan, pemasok, dan pihak terkait.</p>
</div>
<div class="sig-block">
<p>Direktur/Pemilik,</p>
<div class="sig-line"></div>
<p>(Nama & Tanda Tangan)</p>
</div>

<!-- 2. TIM MANAJEMEN HALAL -->
<h2>2. Tim Manajemen Halal</h2>
<table>
<tr><th>No</th><th>Nama</th><th>Jabatan</th><th>Peran dalam Tim Halal</th></tr>
{$teamRows}
</table>

<!-- 3. PELATIHAN DAN EDUKASI -->
<h2>3. Pelatihan dan Edukasi</h2>
<table>
<tr><th>No</th><th>Tanggal</th><th>Materi</th><th>Penyelenggara</th><th>Peserta</th></tr>
{$trainingRows}
</table>

<!-- 4. BAHAN -->
<h2>4. Bahan</h2>
<p><span class="stat">Total bahan: <b>{$totalIng}</b></span> <span class="stat">Bersertifikat: <b>{$certIng}</b></span> <span class="stat">Alami halal: <b>{$noRiskIng}</b></span> <span class="stat">Belum ada: <b>{$missingIng}</b></span></p>
<table>
<tr><th>No</th><th>Nama Bahan</th><th>Merek/Produsen</th><th>Tingkat Risiko</th><th>No SH</th><th>Status</th></tr>
{$ingredientRows}
</table>

<!-- 5. PRODUK -->
<h2>5. Produk</h2>
<table>
<tr><th>No</th><th>Nama Produk</th><th>Kode</th><th>Skor Halal</th><th>Status</th><th>Bahan</th></tr>
{$productRows}
</table>

<!-- 6. FASILITAS PRODUKSI -->
<h2>6. Fasilitas Produksi</h2>
<table>
<tr><th>Item</th><th>Detail</th></tr>
<tr><td>Nama Fasilitas</td><td>{$facilityName}</td></tr>
<tr><td>Kode</td><td>{$facilityCode}</td></tr>
<tr><td>Alamat</td><td>{$facilityAddr}</td></tr>
<tr><td>Kapasitas Produksi</td><td>{$capacity}</td></tr>
<tr><td>Penanggung Jawab</td><td>{$pic}</td></tr>
</table>

<!-- 7. PROSEDUR AKTIVITAS KRITIS -->
<h2>7. Prosedur Aktivitas Kritis</h2>
<h3>7.1 Penerimaan Bahan</h3>
<ol>
<li>Periksa label halal pada kemasan bahan</li>
<li>Cocokkan dengan Daftar Bahan yang disetujui dan nomor SH</li>
<li>Tolak bahan yang tidak sesuai atau tidak memiliki sertifikat halal</li>
<li>Catat dalam logbook penerimaan bahan</li>
</ol>
<h3>7.2 Penyimpanan</h3>
<ol>
<li>Simpan bahan halal terpisah dari bahan non-halal (jika ada)</li>
<li>Beri label/identifikasi yang jelas pada setiap bahan</li>
<li>Terapkan sistem FIFO (First In First Out)</li>
</ol>
<h3>7.3 Produksi</h3>
<ol>
<li>Pastikan peralatan bersih sebelum digunakan</li>
<li>Gunakan hanya bahan yang ada dalam Daftar Bahan yang disetujui</li>
<li>Hindari kontaminasi silang dengan produk/bahan non-halal</li>
</ol>
<h3>7.4 Pengemasan</h3>
<ol>
<li>Pastikan kemasan dalam kondisi bersih</li>
<li>Cantumkan label halal sesuai ketentuan BPJPH</li>
<li>Cantumkan informasi batch, tanggal produksi, dan tanggal kedaluwarsa</li>
</ol>

<!-- 8. KEMAMPUAN TELUSUR -->
<h2>8. Kemampuan Telusur</h2>
<ol>
<li><strong>Sistem Batch:</strong> Setiap lot produksi memiliki nomor batch unik yang mencatat seluruh bahan yang digunakan.</li>
<li><strong>Alur Telusur:</strong> Bahan Baku (lot pemasok) → Proses Produksi (batch) → Produk Jadi (label) → Distribusi (surat jalan).</li>
<li><strong>Target:</strong> Seluruh bahan hingga produk jadi dapat ditelusuri dalam waktu 2 jam.</li>
<li><strong>Masa Simpan Dokumen:</strong> Minimal 2 tahun untuk seluruh catatan produksi, penerimaan, dan distribusi.</li>
</ol>

<!-- 9. PENANGANAN PRODUK TIDAK HALAL -->
<h2>9. Penanganan Produk Tidak Memenuhi Kriteria Halal</h2>
<ol>
<li><strong>Identifikasi:</strong> Produk/bahan yang terindikasi tidak halal segera diidentifikasi dan dicatat.</li>
<li><strong>Karantina:</strong> Produk dipisahkan, diberi label "KARANTINA", disimpan di area terpisah.</li>
<li><strong>Investigasi:</strong> Tim halal melakukan investigasi penyebab ketidaksesuaian.</li>
<li><strong>Tindakan:</strong> Produk tidak halal dimusnahkan. Peralatan terkontaminasi dibersihkan.</li>
<li><strong>Korektif:</strong> Identifikasi akar masalah, perbaiki prosedur, lakukan pelatihan ulang.</li>
<li><strong>Recall:</strong> Jika produk sudah didistribusikan, lakukan penarikan dan hubungi seluruh penerima.</li>
</ol>

<!-- 10. AUDIT INTERNAL -->
<h2>10. Audit Internal</h2>
<p>Audit internal halal dilaksanakan minimal <strong>1 kali per 6 bulan</strong> oleh tim halal internal atau pihak ketiga yang kompeten.</p>
<p>Ruang lingkup audit meliputi seluruh aspek SJPH: kebijakan, bahan, proses produksi, fasilitas, ketelusuran, dan dokumentasi.</p>
<p>Hasil audit didokumentasikan dalam laporan yang mencakup temuan, kategori (mayor/minor), tindakan korektif, dan target penyelesaian.</p>

<!-- 11. KAJI ULANG MANAJEMEN -->
<h2>11. Kaji Ulang Manajemen</h2>
<p>Kaji ulang manajemen dilaksanakan minimal <strong>1 kali per tahun</strong> dengan agenda:</p>
<ol>
<li>Evaluasi pelaksanaan SJPH periode sebelumnya</li>
<li>Hasil audit internal halal dan status tindakan korektif</li>
<li>Evaluasi status sertifikat halal bahan</li>
<li>Keluhan pelanggan terkait kehalalan produk</li>
<li>Perubahan regulasi halal yang mempengaruhi perusahaan</li>
<li>Rencana perbaikan dan peningkatan SJPH</li>
</ol>
<div class="sig-block">
<p>Mengetahui,</p>
<div class="sig-line"></div>
<p>Direktur/Pemilik</p>
</div>

<hr>
<p style="text-align:center;font-size:9pt;color:#999;">Dokumen ini dihasilkan oleh HalalPass 2026 pada {$today}<br>Berdasarkan UU No. 33/2014 dan PP No. 42/2024</p>

</body>
</html>
HTML;
    }
}