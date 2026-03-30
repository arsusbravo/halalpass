<?php

namespace Database\Seeders;

use App\Models\SjphDocument;
use Illuminate\Database\Seeder;

class SjphDocumentSeeder extends Seeder
{
    public function run(): void
    {
        // Company 1, Facility 1 — in progress (7 of 11 filled)
        SjphDocument::create([
            'company_id' => 1,
            'facility_id' => 1,
            'version' => '1.0',
            'status' => 'draft',
            'kebijakan_halal' => json_encode([
                'policy_statement' => 'PT Berkah Pangan Nusantara berkomitmen untuk memproduksi produk halal sesuai syariat Islam dan peraturan perundang-undangan yang berlaku.',
                'signed_by' => 'Budi Santoso',
                'signed_date' => '2025-11-01',
            ]),
            'tim_manajemen_halal' => json_encode([
                'coordinator' => 'Siti Rahmawati',
                'members' => [
                    ['name' => 'Hendra Wijaya', 'role' => 'QA Manager', 'phone' => '08121234567'],
                    ['name' => 'Ahmad Fauzi', 'role' => 'Production Manager', 'phone' => '08129876543'],
                    ['name' => 'Rini Puspita', 'role' => 'Procurement', 'phone' => '08131112222'],
                ],
                'training_completed' => true,
            ]),
            'pelatihan_edukasi' => json_encode([
                'last_training_date' => '2025-09-15',
                'training_provider' => 'LPPOM MUI Jawa Barat',
                'attendees_count' => 25,
                'topics' => ['Dasar-dasar produk halal', 'Titik kritis kehalalan', 'Prosedur audit internal'],
                'next_training_date' => '2026-03-15',
            ]),
            'bahan' => json_encode([
                'total_ingredients' => 20,
                'all_certified' => false,
                'expired_count' => 1,
                'missing_count' => 1,
                'notes' => 'Cabai merah bubuk perlu perpanjangan SH. Tomat pasta belum memiliki SH.',
            ]),
            'produk' => json_encode([
                'products' => [
                    ['name' => 'Mie Goreng Spesial', 'code' => 'PRD-001', 'status' => 'at_risk'],
                    ['name' => 'Mie Kuah Soto', 'code' => 'PRD-002', 'status' => 'compliant'],
                    ['name' => 'Saus Sambal Extra Pedas', 'code' => 'PRD-003', 'status' => 'non_compliant'],
                    ['name' => 'Kecap Manis Premium', 'code' => 'PRD-004', 'status' => 'compliant'],
                    ['name' => 'Puding Cup Stroberi', 'code' => 'PRD-005', 'status' => 'pending'],
                ],
            ]),
            'fasilitas_produksi' => json_encode([
                'facility_name' => 'Pabrik Utama Bekasi',
                'production_lines' => [
                    ['name' => 'Line A - Mie Instan', 'capacity' => '30 ton/bulan', 'dedicated_halal' => true],
                    ['name' => 'Line B - Bumbu & Saus', 'capacity' => '20 ton/bulan', 'dedicated_halal' => true],
                ],
                'cleaning_procedure' => 'Pembersihan menyeluruh setiap pergantian produk. Tidak ada produk non-halal di fasilitas ini.',
                'shared_with_non_halal' => false,
            ]),
            'prosedur_aktivitas_kritis' => json_encode([
                'critical_points' => [
                    ['point' => 'Penerimaan bahan baku', 'control' => 'Cek SH dan CoA sebelum diterima'],
                    ['point' => 'Pencampuran bumbu', 'control' => 'Verifikasi semua bahan sesuai formula halal'],
                    ['point' => 'Penggorengan', 'control' => 'Pastikan minyak dari sumber halal bersertifikat'],
                    ['point' => 'Pengemasan', 'control' => 'Pisahkan area pengemasan dari produk non-halal'],
                ],
            ]),
            // kemampuan_telusur — NOT YET FILLED
            // penanganan_produk_tidak_halal — NOT YET FILLED
            // audit_internal — NOT YET FILLED
            // kaji_ulang_manajemen — NOT YET FILLED
        ]);

        // Company 1, Facility 3 (Tangerang) — completed & approved
        SjphDocument::create([
            'company_id' => 1,
            'facility_id' => 3,
            'version' => '2.1',
            'status' => 'approved',
            'kebijakan_halal' => json_encode(['policy_statement' => 'Komitmen halal Pabrik Saus Tangerang.', 'signed_by' => 'Budi Santoso', 'signed_date' => '2025-06-01']),
            'tim_manajemen_halal' => json_encode(['coordinator' => 'Dedi Kurniawan', 'members' => [['name' => 'Ari', 'role' => 'QA'], ['name' => 'Nina', 'role' => 'Production']]]),
            'pelatihan_edukasi' => json_encode(['last_training_date' => '2025-08-20', 'attendees_count' => 12]),
            'bahan' => json_encode(['total_ingredients' => 15, 'all_certified' => true]),
            'produk' => json_encode(['products' => [['name' => 'Kecap Manis Premium', 'status' => 'compliant']]]),
            'fasilitas_produksi' => json_encode(['facility_name' => 'Pabrik Saus Tangerang', 'shared_with_non_halal' => false]),
            'prosedur_aktivitas_kritis' => json_encode(['critical_points' => [['point' => 'Penerimaan bahan', 'control' => 'Cek SH']]]),
            'kemampuan_telusur' => json_encode(['system' => 'Batch numbering', 'lot_tracking' => true, 'retention_period' => '2 tahun']),
            'penanganan_produk_tidak_halal' => json_encode(['procedure' => 'Karantina → investigasi → pemusnahan/rework', 'notification' => 'Lapor ke BPJPH dalam 24 jam']),
            'audit_internal' => json_encode(['last_audit_date' => '2025-10-15', 'auditor' => 'Tim QA Internal', 'findings' => 0, 'next_audit' => '2026-04-15']),
            'kaji_ulang_manajemen' => json_encode(['last_review_date' => '2025-11-01', 'reviewed_by' => 'Budi Santoso', 'action_items' => ['Perpanjangan SH minyak goreng', 'Training ulang tim baru']]),
            'approved_by' => 1,
            'approved_at' => '2025-11-15 10:00:00',
        ]);

        // Company 2 — just started
        SjphDocument::create([
            'company_id' => 2,
            'facility_id' => 4,
            'version' => '1.0',
            'status' => 'draft',
            'kebijakan_halal' => json_encode([
                'policy_statement' => 'PT Sari Rasa Sejahtera menjamin kehalalan seluruh produk minuman.',
                'signed_by' => 'Dewi Lestari',
                'signed_date' => '2026-01-10',
            ]),
            'tim_manajemen_halal' => json_encode([
                'coordinator' => 'Wahyu Pratama',
                'members' => [['name' => 'Dewi Lestari', 'role' => 'Director']],
            ]),
            // Only 2 out of 11 filled = 18% completion
        ]);
    }
}