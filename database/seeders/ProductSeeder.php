<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ============================================================
        //  Company 1 products
        // ============================================================

        Product::create([
            'id' => 1,
            'company_id' => 1,
            'facility_id' => 1,
            'name' => 'Mie Goreng Spesial Berkah',
            'code' => 'PRD-001',
            'brand' => 'Berkah',
            'description' => 'Mie goreng instan rasa spesial dengan bumbu khas nusantara',
            'category' => 'makanan',
            'halal_status' => 'at_risk',
            'halal_health_score' => 70,
            'halal_status_checked_at' => $now->copy()->subDays(2),
            'status' => 'active',
        ]);

        Product::create([
            'id' => 2,
            'company_id' => 1,
            'facility_id' => 1,
            'name' => 'Mie Kuah Soto Berkah',
            'code' => 'PRD-002',
            'brand' => 'Berkah',
            'description' => 'Mie instan kuah rasa soto ayam',
            'category' => 'makanan',
            'halal_status' => 'compliant',
            'halal_health_score' => 100,
            'halal_status_checked_at' => $now->copy()->subDays(2),
            'status' => 'active',
        ]);

        Product::create([
            'id' => 3,
            'company_id' => 1,
            'facility_id' => 3,
            'name' => 'Saus Sambal Extra Pedas',
            'code' => 'PRD-003',
            'brand' => 'Berkah',
            'description' => 'Saus sambal extra pedas untuk mie dan makanan',
            'category' => 'makanan',
            'halal_status' => 'non_compliant',
            'halal_health_score' => 0,
            'halal_status_checked_at' => $now->copy()->subDays(1),
            'status' => 'active',
        ]);

        Product::create([
            'id' => 4,
            'company_id' => 1,
            'facility_id' => 3,
            'name' => 'Kecap Manis Berkah Premium',
            'code' => 'PRD-004',
            'brand' => 'Berkah',
            'description' => 'Kecap manis premium dengan formula tradisional',
            'category' => 'makanan',
            'halal_status' => 'compliant',
            'halal_health_score' => 100,
            'halal_status_checked_at' => $now->copy()->subDays(3),
            'status' => 'active',
        ]);

        Product::create([
            'id' => 5,
            'company_id' => 1,
            'facility_id' => 2,
            'name' => 'Puding Cup Rasa Stroberi',
            'code' => 'PRD-005',
            'brand' => 'Berkah Kids',
            'description' => 'Puding cup siap makan untuk anak-anak',
            'category' => 'makanan',
            'halal_status' => 'pending',
            'halal_health_score' => 0,
            'halal_status_checked_at' => null,
            'status' => 'active',
        ]);

        // ============================================================
        //  Company 2 products
        // ============================================================

        Product::create([
            'id' => 6,
            'company_id' => 2,
            'facility_id' => 4,
            'name' => 'Sirup Rasa Cocopandan',
            'code' => 'PRD-001',
            'brand' => 'Sari Rasa',
            'description' => 'Sirup rasa cocopandan untuk minuman segar',
            'category' => 'minuman',
            'halal_status' => 'non_compliant',
            'halal_health_score' => 0,
            'halal_status_checked_at' => $now->copy()->subDays(1),
            'status' => 'active',
        ]);

        Product::create([
            'id' => 7,
            'company_id' => 2,
            'facility_id' => 4,
            'name' => 'Minuman Teh Manis Botol',
            'code' => 'PRD-002',
            'brand' => 'Sari Rasa',
            'description' => 'Teh manis siap minum dalam botol 350ml',
            'category' => 'minuman',
            'halal_status' => 'compliant',
            'halal_health_score' => 100,
            'halal_status_checked_at' => $now->copy()->subDays(2),
            'status' => 'active',
        ]);
    }
}