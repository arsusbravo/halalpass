<?php

namespace Database\Seeders;

use App\Models\HalalCertificate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HalalCertificateSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ============================================================
        //  Company 1 certificates — mixed statuses for demo
        // ============================================================

        // Tepung Terigu — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 1,
            'sh_number' => 'LPPOM-00012345-MUI-0124',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(8),
            'expiry_date' => $now->copy()->addMonths(16),
            'document_path' => 'certificates/company-1/tepung-terigu-sh.pdf',
            'original_filename' => 'SH_Tepung_Terigu_Bogasari.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // Tepung Tapioka — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 2,
            'sh_number' => 'LPPOM-00023456-MUI-0224',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(6),
            'expiry_date' => $now->copy()->addMonths(18),
            'document_path' => 'certificates/company-1/tepung-tapioka-sh.pdf',
            'original_filename' => 'SH_Tapioka_BudiStarch.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // Minyak Goreng — expiring_soon (< 90 days)
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 3,
            'sh_number' => 'LPPOM-00034567-MUI-0623',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(22),
            'expiry_date' => $now->copy()->addDays(45),
            'document_path' => 'certificates/company-1/minyak-sawit-sh.pdf',
            'original_filename' => 'SH_Minyak_Sawit_Bimoli.pdf',
            'status' => 'expiring_soon',
            'uploaded_by' => 2,
        ]);

        // Garam — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 4,
            'sh_number' => 'LPPOM-00045678-MUI-0324',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(4),
            'expiry_date' => $now->copy()->addMonths(20),
            'document_path' => 'certificates/company-1/garam-sh.pdf',
            'original_filename' => 'SH_Garam_PTGaram.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // MSG — foreign HCB, valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 5,
            'sh_number' => 'CICOT-HF-2024-08821',
            'issuing_body' => 'FOREIGN_HCB',
            'issuing_body_name' => 'The Central Islamic Council of Thailand (CICOT)',
            'issue_date' => $now->copy()->subMonths(10),
            'expiry_date' => $now->copy()->addMonths(14),
            'document_path' => 'certificates/company-1/msg-cicot-sh.pdf',
            'original_filename' => 'Halal_Cert_Ajinomoto_Thailand.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // Bawang Putih Bubuk — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 6,
            'sh_number' => 'LPPOM-00056789-MUI-0524',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(3),
            'expiry_date' => $now->copy()->addMonths(21),
            'document_path' => 'certificates/company-1/bawang-putih-sh.pdf',
            'original_filename' => 'SH_BawangPutih_BumbuNusantara.pdf',
            'status' => 'valid',
            'uploaded_by' => 3,
        ]);

        // Cabai Merah Bubuk — EXPIRED!
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 7,
            'sh_number' => 'LPPOM-00067890-MUI-0322',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(26),
            'expiry_date' => $now->copy()->subDays(15),
            'document_path' => 'certificates/company-1/cabai-merah-sh-old.pdf',
            'original_filename' => 'SH_Cabai_Merah_EXPIRED.pdf',
            'status' => 'expired',
            'uploaded_by' => 2,
        ]);

        // STPP — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 8,
            'sh_number' => 'LPH-JKT-00078901-0124',
            'issuing_body' => 'LPH',
            'issuing_body_name' => 'LPH Universitas Indonesia',
            'issue_date' => $now->copy()->subMonths(7),
            'expiry_date' => $now->copy()->addMonths(17),
            'document_path' => 'certificates/company-1/stpp-sh.pdf',
            'original_filename' => 'SH_STPP_KimiaFarma.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // Tartrazine — expiring_soon (< 90 days)
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 9,
            'sh_number' => 'LPH-JKT-00089012-0623',
            'issuing_body' => 'LPH',
            'issuing_body_name' => 'LPH Universitas Indonesia',
            'issue_date' => $now->copy()->subMonths(21),
            'expiry_date' => $now->copy()->addDays(60),
            'document_path' => 'certificates/company-1/tartrazine-sh.pdf',
            'original_filename' => 'SH_Tartrazine_KimiaFarma.pdf',
            'status' => 'expiring_soon',
            'uploaded_by' => 1,
        ]);

        // Gelatin Halal — foreign HCB, valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 10,
            'sh_number' => 'SANHA-PK-2024-H-4421',
            'issuing_body' => 'FOREIGN_HCB',
            'issuing_body_name' => 'SANHA Halal Associates (Pakistan)',
            'issue_date' => $now->copy()->subMonths(5),
            'expiry_date' => $now->copy()->addMonths(19),
            'document_path' => 'certificates/company-1/gelatin-sanha-sh.pdf',
            'original_filename' => 'Halal_Cert_AlBaraka_Gelatin.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // Kecap Manis (child of Bumbu Mie Goreng) — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 12,
            'sh_number' => 'LPPOM-00091234-MUI-0924',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(2),
            'expiry_date' => $now->copy()->addMonths(22),
            'document_path' => 'certificates/company-1/kecap-bango-sh.pdf',
            'original_filename' => 'SH_KecapManis_Bango.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // Minyak Wijen (child) — expiring_soon
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 13,
            'sh_number' => 'JAKIM-HALAL-2023-LKK-0088',
            'issuing_body' => 'FOREIGN_HCB',
            'issuing_body_name' => 'JAKIM Malaysia',
            'issue_date' => $now->copy()->subMonths(20),
            'expiry_date' => $now->copy()->addDays(30),
            'document_path' => 'certificates/company-1/minyak-wijen-jakim.pdf',
            'original_filename' => 'Halal_Cert_LeeKumKee_Sesame.pdf',
            'status' => 'expiring_soon',
            'uploaded_by' => 2,
        ]);

        // Bawang Merah Goreng (child) — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 14,
            'sh_number' => 'LPPOM-00101234-MUI-0724',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(1),
            'expiry_date' => $now->copy()->addMonths(23),
            'document_path' => 'certificates/company-1/bawang-merah-sh.pdf',
            'original_filename' => 'SH_BawangMerah_BumbuNusantara.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // Cabai Rawit Giling (child of Premix Sambal) — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 16,
            'sh_number' => 'LPPOM-00112345-MUI-0824',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(1),
            'expiry_date' => $now->copy()->addMonths(23),
            'document_path' => null,
            'original_filename' => null,
            'status' => 'valid',
            'notes' => 'Dokumen fisik ada, belum di-scan',
            'uploaded_by' => 3,
        ]);

        // Tomat Pasta (child) — NO CERTIFICATE (ingredient 17 has no cert = "missing")

        // Asam Sitrat (child) — valid
        HalalCertificate::create([
            'company_id' => 1,
            'ingredient_id' => 18,
            'sh_number' => 'LPH-JKT-00123456-1024',
            'issuing_body' => 'LPH',
            'issuing_body_name' => 'LPH Universitas Indonesia',
            'issue_date' => $now->copy()->subMonths(2),
            'expiry_date' => $now->copy()->addMonths(22),
            'document_path' => 'certificates/company-1/asam-sitrat-sh.pdf',
            'original_filename' => 'SH_AsamSitrat_KimiaFarma.pdf',
            'status' => 'valid',
            'uploaded_by' => 1,
        ]);

        // ============================================================
        //  Company 2 certificates
        // ============================================================

        // Gula Pasir — valid
        HalalCertificate::create([
            'company_id' => 2,
            'ingredient_id' => 19,
            'sh_number' => 'LPPOM-00201234-MUI-0424',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(5),
            'expiry_date' => $now->copy()->addMonths(19),
            'document_path' => 'certificates/company-2/gula-pasir-sh.pdf',
            'original_filename' => 'SH_GulaPasir_Gulaku.pdf',
            'status' => 'valid',
            'uploaded_by' => 5,
        ]);

        // High Fructose Syrup — EXPIRED
        HalalCertificate::create([
            'company_id' => 2,
            'ingredient_id' => 20,
            'sh_number' => 'LPPOM-00212345-MUI-0122',
            'issuing_body' => 'MUI',
            'issuing_body_name' => 'LPPOM MUI',
            'issue_date' => $now->copy()->subMonths(28),
            'expiry_date' => $now->copy()->subMonths(4),
            'document_path' => 'certificates/company-2/hfs-sh-old.pdf',
            'original_filename' => 'SH_HFS_EXPIRED.pdf',
            'status' => 'expired',
            'uploaded_by' => 5,
        ]);
    }
}