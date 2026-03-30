<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'id' => 1,
            'name' => 'PT Berkah Pangan Nusantara',
            'npwp' => '01.234.567.8-012.000',
            'bpjph_registration_number' => 'BPJPH-2025-00142',
            'address' => 'Jl. Raya Industri No. 88, Kawasan Industri Jababeka',
            'city' => 'Bekasi',
            'province' => 'Jawa Barat',
            'postal_code' => '17530',
            'phone' => '021-89831234',
            'email' => 'info@berkahpangan.co.id',
            'pic_name' => 'Budi Santoso',
            'pic_phone' => '08121234567',
            'status' => 'active',
        ]);

        Company::create([
            'id' => 2,
            'name' => 'PT Sari Rasa Sejahtera',
            'npwp' => '02.345.678.9-023.000',
            'bpjph_registration_number' => 'BPJPH-2025-00287',
            'address' => 'Jl. Industri Raya Blok C5 No. 12, PIER',
            'city' => 'Pasuruan',
            'province' => 'Jawa Timur',
            'postal_code' => '67152',
            'phone' => '0343-741234',
            'email' => 'admin@sarirasa.co.id',
            'pic_name' => 'Dewi Lestari',
            'pic_phone' => '08219876543',
            'status' => 'active',
        ]);
    }
}