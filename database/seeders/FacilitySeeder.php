<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        // Company 1 facilities
        Facility::create([
            'id' => 1,
            'company_id' => 1,
            'name' => 'Pabrik Utama Bekasi',
            'code' => 'FAC-BKS-01',
            'address' => 'Jl. Raya Industri No. 88, Blok A',
            'city' => 'Bekasi',
            'province' => 'Jawa Barat',
            'postal_code' => '17530',
            'phone' => '021-89831234',
            'pic_name' => 'Hendra Wijaya',
            'production_capacity' => '50 ton/bulan - produksi mie instan & bumbu',
            'sjph_status' => 'in_progress',
            'status' => 'active',
        ]);

        Facility::create([
            'id' => 2,
            'company_id' => 1,
            'name' => 'Gudang & Packaging Cikarang',
            'code' => 'FAC-CKR-01',
            'address' => 'Jl. Niaga Raya Blok D No. 15, Lippo Cikarang',
            'city' => 'Bekasi',
            'province' => 'Jawa Barat',
            'postal_code' => '17550',
            'phone' => '021-89734567',
            'pic_name' => 'Rini Puspita',
            'production_capacity' => '30 ton/bulan - packaging & cold storage',
            'sjph_status' => 'not_started',
            'status' => 'active',
        ]);

        Facility::create([
            'id' => 3,
            'company_id' => 1,
            'name' => 'Pabrik Saus & Sambal Tangerang',
            'code' => 'FAC-TNG-01',
            'address' => 'Jl. Gatot Subroto Km 7 No. 22',
            'city' => 'Tangerang',
            'province' => 'Banten',
            'postal_code' => '15135',
            'phone' => '021-55781234',
            'pic_name' => 'Dedi Kurniawan',
            'production_capacity' => '20 ton/bulan - saus, sambal, kecap',
            'sjph_status' => 'completed',
            'status' => 'active',
        ]);

        // Company 2 facility
        Facility::create([
            'id' => 4,
            'company_id' => 2,
            'name' => 'Pabrik Utama Pasuruan',
            'code' => 'FAC-PSR-01',
            'address' => 'Jl. Industri Raya Blok C5 No. 12',
            'city' => 'Pasuruan',
            'province' => 'Jawa Timur',
            'postal_code' => '67152',
            'phone' => '0343-741234',
            'pic_name' => 'Wahyu Pratama',
            'production_capacity' => '80 ton/bulan - minuman & sirup',
            'sjph_status' => 'in_progress',
            'status' => 'active',
        ]);
    }
}