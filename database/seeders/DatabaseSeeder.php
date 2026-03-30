<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            UserSeeder::class,
            FacilitySeeder::class,
            SupplierSeeder::class,
            IngredientSeeder::class,
            HalalCertificateSeeder::class,
            ProductSeeder::class,
            ProductIngredientSeeder::class,
            SupplierAccessTokenSeeder::class,
            SjphDocumentSeeder::class,
        ]);
    }
}