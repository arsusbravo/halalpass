<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductIngredientSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // ========================================
            // Product 1: Mie Goreng Spesial Berkah
            // (at_risk — because Minyak Wijen cert is expiring_soon)
            // ========================================
            ['product_id' => 1, 'ingredient_id' => 1,  'percentage' => 55.0000, 'is_critical' => false, 'usage_purpose' => 'Bahan utama mie',         'sort_order' => 1],
            ['product_id' => 1, 'ingredient_id' => 2,  'percentage' => 10.0000, 'is_critical' => false, 'usage_purpose' => 'Campuran adonan mie',      'sort_order' => 2],
            ['product_id' => 1, 'ingredient_id' => 3,  'percentage' => 8.0000,  'is_critical' => false, 'usage_purpose' => 'Penggorengan mie',          'sort_order' => 3],
            ['product_id' => 1, 'ingredient_id' => 4,  'percentage' => 2.0000,  'is_critical' => false, 'usage_purpose' => 'Penyedap adonan',           'sort_order' => 4],
            ['product_id' => 1, 'ingredient_id' => 8,  'percentage' => 0.5000,  'is_critical' => false, 'usage_purpose' => 'Pengenyal mie',             'sort_order' => 5],
            ['product_id' => 1, 'ingredient_id' => 9,  'percentage' => 0.1000,  'is_critical' => false, 'usage_purpose' => 'Pewarna mie',               'sort_order' => 6],
            ['product_id' => 1, 'ingredient_id' => 11, 'percentage' => 24.4000, 'is_critical' => true,  'usage_purpose' => 'Bumbu mie goreng (sachet)', 'sort_order' => 7],

            // ========================================
            // Product 2: Mie Kuah Soto Berkah
            // (compliant — all certs valid)
            // ========================================
            ['product_id' => 2, 'ingredient_id' => 1,  'percentage' => 58.0000, 'is_critical' => false, 'usage_purpose' => 'Bahan utama mie',     'sort_order' => 1],
            ['product_id' => 2, 'ingredient_id' => 2,  'percentage' => 12.0000, 'is_critical' => false, 'usage_purpose' => 'Campuran adonan',      'sort_order' => 2],
            ['product_id' => 2, 'ingredient_id' => 4,  'percentage' => 3.0000,  'is_critical' => false, 'usage_purpose' => 'Bumbu kuah',           'sort_order' => 3],
            ['product_id' => 2, 'ingredient_id' => 5,  'percentage' => 2.0000,  'is_critical' => false, 'usage_purpose' => 'Penyedap kuah',        'sort_order' => 4],
            ['product_id' => 2, 'ingredient_id' => 6,  'percentage' => 1.5000,  'is_critical' => false, 'usage_purpose' => 'Bumbu kuah soto',      'sort_order' => 5],
            ['product_id' => 2, 'ingredient_id' => 8,  'percentage' => 0.5000,  'is_critical' => false, 'usage_purpose' => 'Pengenyal mie',        'sort_order' => 6],
            ['product_id' => 2, 'ingredient_id' => 9,  'percentage' => 0.1000,  'is_critical' => false, 'usage_purpose' => 'Pewarna mie',          'sort_order' => 7],

            // ========================================
            // Product 3: Saus Sambal Extra Pedas
            // (non_compliant — Cabai Merah expired + Tomat Pasta has no cert)
            // ========================================
            ['product_id' => 3, 'ingredient_id' => 7,  'percentage' => 20.0000, 'is_critical' => true,  'usage_purpose' => 'Bahan utama rasa pedas',  'sort_order' => 1],
            ['product_id' => 3, 'ingredient_id' => 15, 'percentage' => 50.0000, 'is_critical' => true,  'usage_purpose' => 'Premix saus sambal base',  'sort_order' => 2],
            ['product_id' => 3, 'ingredient_id' => 4,  'percentage' => 5.0000,  'is_critical' => false, 'usage_purpose' => 'Penyedap',                'sort_order' => 3],
            ['product_id' => 3, 'ingredient_id' => 5,  'percentage' => 2.0000,  'is_critical' => false, 'usage_purpose' => 'Penambah rasa',            'sort_order' => 4],
            ['product_id' => 3, 'ingredient_id' => 6,  'percentage' => 3.0000,  'is_critical' => false, 'usage_purpose' => 'Bumbu',                    'sort_order' => 5],
            ['product_id' => 3, 'ingredient_id' => 3,  'percentage' => 15.0000, 'is_critical' => false, 'usage_purpose' => 'Minyak saus',              'sort_order' => 6],

            // ========================================
            // Product 4: Kecap Manis Berkah Premium
            // (compliant)
            // ========================================
            ['product_id' => 4, 'ingredient_id' => 4,  'percentage' => 15.0000, 'is_critical' => false, 'usage_purpose' => 'Bahan kecap',  'sort_order' => 1],
            ['product_id' => 4, 'ingredient_id' => 5,  'percentage' => 1.0000,  'is_critical' => false, 'usage_purpose' => 'Penambah rasa', 'sort_order' => 2],

            // ========================================
            // Product 5: Puding Cup Stroberi
            // (pending — uses gelatin which is critical)
            // ========================================
            ['product_id' => 5, 'ingredient_id' => 10, 'percentage' => 8.0000,  'is_critical' => true,  'usage_purpose' => 'Pengental/gelling agent',  'sort_order' => 1],
            ['product_id' => 5, 'ingredient_id' => 4,  'percentage' => 1.0000,  'is_critical' => false, 'usage_purpose' => 'Penyeimbang rasa',         'sort_order' => 2],
            ['product_id' => 5, 'ingredient_id' => 9,  'percentage' => 0.0500,  'is_critical' => false, 'usage_purpose' => 'Pewarna',                  'sort_order' => 3],

            // ========================================
            // Company 2: Sirup Cocopandan
            // (non_compliant — HFS cert expired)
            // ========================================
            ['product_id' => 6, 'ingredient_id' => 19, 'percentage' => 40.0000, 'is_critical' => false, 'usage_purpose' => 'Pemanis utama',  'sort_order' => 1],
            ['product_id' => 6, 'ingredient_id' => 20, 'percentage' => 30.0000, 'is_critical' => false, 'usage_purpose' => 'Pemanis tambahan', 'sort_order' => 2],

            // ========================================
            // Company 2: Teh Manis Botol
            // (compliant)
            // ========================================
            ['product_id' => 7, 'ingredient_id' => 19, 'percentage' => 12.0000, 'is_critical' => false, 'usage_purpose' => 'Pemanis', 'sort_order' => 1],
        ];

        $now = now();

        foreach ($rows as $row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            DB::table('product_ingredients')->insert($row);
        }
    }
}