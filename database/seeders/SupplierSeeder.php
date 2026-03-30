<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        // Company 1 suppliers
        Supplier::create([
            'id' => 1,
            'company_id' => 1,
            'name' => 'PT Tepung Mas Indonesia',
            'code' => 'SUP-001',
            'address' => 'Jl. Raya Surabaya-Mojokerto Km 19',
            'city' => 'Mojokerto',
            'country' => 'ID',
            'phone' => '0321-321456',
            'email' => 'sales@tepungmas.co.id',
            'pic_name' => 'Iwan Setiawan',
            'pic_phone' => '08123456789',
            'status' => 'active',
            'notes' => 'Supplier utama tepung terigu & tapioka',
        ]);

        Supplier::create([
            'id' => 2,
            'company_id' => 1,
            'name' => 'CV Bumbu Nusantara',
            'code' => 'SUP-002',
            'address' => 'Jl. Pasar Besar No. 45',
            'city' => 'Surabaya',
            'country' => 'ID',
            'phone' => '031-5678901',
            'email' => 'order@bumbunusantara.com',
            'pic_name' => 'Yanti Susilo',
            'pic_phone' => '08219876543',
            'status' => 'active',
            'notes' => 'Rempah-rempah, bumbu kering, penyedap rasa',
        ]);

        Supplier::create([
            'id' => 3,
            'company_id' => 1,
            'name' => 'Golden Dragon Trading Co., Ltd.',
            'code' => 'SUP-003',
            'address' => '88 Soi Sukhumvit 42, Prakanong',
            'city' => 'Bangkok',
            'country' => 'TH',
            'phone' => '+66-2-381-1234',
            'email' => 'export@goldendragontrading.th',
            'pic_name' => 'Somchai Tanaka',
            'pic_phone' => '+66812345678',
            'status' => 'active',
            'notes' => 'MSG, flavor enhancers, food additives - import',
        ]);

        Supplier::create([
            'id' => 4,
            'company_id' => 1,
            'name' => 'PT Minyak Sawit Kalimantan',
            'code' => 'SUP-004',
            'address' => 'Jl. Jendral Sudirman No. 12',
            'city' => 'Balikpapan',
            'country' => 'ID',
            'phone' => '0542-871234',
            'email' => 'sales@sawitkaltim.co.id',
            'pic_name' => 'Gunawan',
            'pic_phone' => '08525551234',
            'status' => 'active',
            'notes' => 'Minyak goreng kelapa sawit (CPO & RBDPO)',
        ]);

        Supplier::create([
            'id' => 5,
            'company_id' => 1,
            'name' => 'PT Kimia Farma Ingredients',
            'code' => 'SUP-005',
            'address' => 'Jl. Veteran No. 9',
            'city' => 'Jakarta Pusat',
            'country' => 'ID',
            'phone' => '021-3847890',
            'email' => 'ingredients@kimiafarma.co.id',
            'pic_name' => 'Dr. Rina Handayani',
            'pic_phone' => '08111234567',
            'status' => 'active',
            'notes' => 'Bahan pengawet, pewarna makanan, emulsifier',
        ]);

        Supplier::create([
            'id' => 6,
            'company_id' => 1,
            'name' => 'Al-Baraka Gelatin Industries',
            'code' => 'SUP-006',
            'address' => 'Plot 15, Halal Industrial Zone',
            'city' => 'Karachi',
            'country' => 'PK',
            'phone' => '+92-21-35678901',
            'email' => 'halal@albarakagelatin.pk',
            'pic_name' => 'Muhammad Farhan',
            'pic_phone' => '+923001234567',
            'status' => 'active',
            'notes' => 'Gelatin halal (bovine) & kolagen - sertifikat SANHA',
        ]);

        // Company 2 supplier
        Supplier::create([
            'id' => 7,
            'company_id' => 2,
            'name' => 'PT Gula Manis Nusantara',
            'code' => 'SUP-001',
            'address' => 'Jl. Raya Cepiring Km 3',
            'city' => 'Kendal',
            'country' => 'ID',
            'phone' => '0294-381567',
            'email' => 'info@gulamanis.co.id',
            'pic_name' => 'Surya Adi',
            'pic_phone' => '08131234567',
            'status' => 'active',
            'notes' => 'Gula pasir, gula cair, fruktosa',
        ]);
    }
}