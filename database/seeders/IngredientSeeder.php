<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        //  Company 1 — PT Berkah Pangan Nusantara
        // ============================================================

        // --- Simple Materials ---

        Ingredient::create([
            'id' => 1,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 1,
            'name' => 'Tepung Terigu',
            'code' => 'BHN-001',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => 'Cakra Kembar',
            'manufacturer' => 'PT Bogasari Flour Mills',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode([
                'protein' => '13%',
                'moisture' => '14% max',
                'grade' => 'high protein',
            ]),
        ]);

        Ingredient::create([
            'id' => 2,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 1,
            'name' => 'Tepung Tapioka',
            'code' => 'BHN-002',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => 'Rose Brand',
            'manufacturer' => 'PT Budi Starch & Sweetener',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode([
                'moisture' => '13% max',
                'starch_content' => '85% min',
            ]),
        ]);

        Ingredient::create([
            'id' => 3,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 4,
            'name' => 'Minyak Goreng Sawit',
            'code' => 'BHN-003',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => 'Bimoli',
            'manufacturer' => 'PT Salim Ivomas Pratama',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode([
                'type' => 'RBDPO (Refined Bleached Deodorized Palm Oil)',
                'ffa' => '0.1% max',
            ]),
        ]);

        Ingredient::create([
            'id' => 4,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 2,
            'name' => 'Garam',
            'code' => 'BHN-004',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'PT Garam (Persero)',
            'category' => 'bahan_tambahan',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode([
                'grade' => 'food grade',
                'iodine' => 'fortified',
                'NaCl' => '97% min',
            ]),
        ]);

        Ingredient::create([
            'id' => 5,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 3,
            'name' => 'Monosodium Glutamate (MSG)',
            'code' => 'BHN-005',
            'type' => 'simple',
            'origin_country' => 'TH',
            'brand' => 'Ajinomoto',
            'manufacturer' => 'Ajinomoto Co. (Thailand)',
            'category' => 'bahan_tambahan',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode([
                'purity' => '99% min',
                'form' => 'crystal',
            ]),
        ]);

        Ingredient::create([
            'id' => 6,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 2,
            'name' => 'Bawang Putih Bubuk',
            'code' => 'BHN-006',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'CV Bumbu Nusantara',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode([
                'mesh' => '80-100',
                'moisture' => '6% max',
            ]),
        ]);

        Ingredient::create([
            'id' => 7,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 2,
            'name' => 'Cabai Merah Bubuk',
            'code' => 'BHN-007',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'CV Bumbu Nusantara',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode([
                'heat_level' => '30,000-50,000 SHU',
                'mesh' => '60-80',
            ]),
        ]);

        Ingredient::create([
            'id' => 8,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 5,
            'name' => 'Sodium Tripolyphosphate (STPP)',
            'code' => 'BHN-008',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'PT Kimia Farma Ingredients',
            'category' => 'bahan_penolong',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode([
                'grade' => 'food grade',
                'form' => 'powder',
            ]),
        ]);

        Ingredient::create([
            'id' => 9,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 5,
            'name' => 'Tartrazine (Pewarna Kuning)',
            'code' => 'BHN-009',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'PT Kimia Farma Ingredients',
            'category' => 'bahan_tambahan',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode([
                'CI_number' => '19140',
                'grade' => 'food grade',
            ]),
        ]);

        Ingredient::create([
            'id' => 10,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => 6,
            'name' => 'Gelatin Halal (Bovine)',
            'code' => 'BHN-010',
            'type' => 'simple',
            'origin_country' => 'PK',
            'brand' => 'Al-Baraka',
            'manufacturer' => 'Al-Baraka Gelatin Industries',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'high_risk',
            'specifications' => json_encode([
                'source' => 'bovine (sapi)',
                'bloom' => '200-220',
                'mesh' => '20-40',
                'halal_critical' => true,
            ]),
        ]);

        // --- Composite Materials (with sub-ingredients) ---

        Ingredient::create([
            'id' => 11,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => null,
            'name' => 'Bumbu Mie Goreng Spesial',
            'code' => 'BHN-011',
            'type' => 'composite',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'PT Berkah Pangan Nusantara (in-house)',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode([
                'form' => 'pasta',
                'shelf_life' => '12 bulan',
            ]),
        ]);

        // Children of Bumbu Mie Goreng
        Ingredient::create([
            'id' => 12,
            'company_id' => 1,
            'parent_id' => 11,
            'supplier_id' => 2,
            'name' => 'Kecap Manis',
            'code' => 'BHN-011-A',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => 'Bango',
            'manufacturer' => 'PT Unilever Indonesia',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'high_risk',
            'specifications' => json_encode(['type' => 'kecap manis premium']),
        ]);

        Ingredient::create([
            'id' => 13,
            'company_id' => 1,
            'parent_id' => 11,
            'supplier_id' => 2,
            'name' => 'Minyak Wijen',
            'code' => 'BHN-011-B',
            'type' => 'simple',
            'origin_country' => 'CN',
            'brand' => 'Lee Kum Kee',
            'manufacturer' => 'Lee Kum Kee (Xinhui)',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'high_risk',
            'specifications' => json_encode(['type' => 'pure sesame oil']),
        ]);

        Ingredient::create([
            'id' => 14,
            'company_id' => 1,
            'parent_id' => 11,
            'supplier_id' => 2,
            'name' => 'Bawang Merah Goreng',
            'code' => 'BHN-011-C',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'CV Bumbu Nusantara',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode(['form' => 'crispy fried']),
        ]);

        // Premix Saus Sambal (composite)
        Ingredient::create([
            'id' => 15,
            'company_id' => 1,
            'parent_id' => null,
            'supplier_id' => null,
            'name' => 'Premix Saus Sambal Pedas',
            'code' => 'BHN-012',
            'type' => 'composite',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'PT Berkah Pangan Nusantara (in-house)',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode([
                'form' => 'liquid paste',
                'heat_level' => 'extra pedas',
            ]),
        ]);

        // Children of Premix Saus Sambal
        Ingredient::create([
            'id' => 16,
            'company_id' => 1,
            'parent_id' => 15,
            'supplier_id' => 2,
            'name' => 'Cabai Rawit Giling',
            'code' => 'BHN-012-A',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'CV Bumbu Nusantara',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode(['heat_level' => '80,000+ SHU']),
        ]);

        Ingredient::create([
            'id' => 17,
            'company_id' => 1,
            'parent_id' => 15,
            'supplier_id' => 2,
            'name' => 'Tomat Pasta',
            'code' => 'BHN-012-B',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'CV Bumbu Nusantara',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode(['brix' => '28-30%']),
        ]);

        Ingredient::create([
            'id' => 18,
            'company_id' => 1,
            'parent_id' => 15,
            'supplier_id' => 5,
            'name' => 'Asam Sitrat',
            'code' => 'BHN-012-C',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'PT Kimia Farma Ingredients',
            'category' => 'bahan_tambahan',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode(['grade' => 'food grade anhydrous']),
        ]);

        // ============================================================
        //  Company 2 — PT Sari Rasa Sejahtera (minuman & sirup)
        // ============================================================

        Ingredient::create([
            'id' => 19,
            'company_id' => 2,
            'parent_id' => null,
            'supplier_id' => 7,
            'name' => 'Gula Pasir',
            'code' => 'BHN-001',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => 'Gulaku',
            'manufacturer' => 'PT Sugar Labinta',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'low_risk',
            'specifications' => json_encode(['grade' => 'SHS-1', 'ICUMSA' => '< 100']),
        ]);

        Ingredient::create([
            'id' => 20,
            'company_id' => 2,
            'parent_id' => null,
            'supplier_id' => 7,
            'name' => 'High Fructose Syrup',
            'code' => 'BHN-002',
            'type' => 'simple',
            'origin_country' => 'ID',
            'brand' => null,
            'manufacturer' => 'PT Gula Manis Nusantara',
            'category' => 'bahan_baku',
            'halal_risk_level' => 'medium_risk',
            'specifications' => json_encode(['fructose' => '55%', 'brix' => '77%']),
        ]);
    }
}