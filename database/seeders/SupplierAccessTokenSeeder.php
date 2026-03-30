<?php

namespace Database\Seeders;

use App\Models\SupplierAccessToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SupplierAccessTokenSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Active token — supplier needs to upload new cert for Cabai Merah (expired)
        SupplierAccessToken::create([
            'company_id' => 1,
            'supplier_id' => 2,
            'ingredient_id' => 7,
            'token' => Str::random(64),
            'expires_at' => $now->copy()->addDays(14),
            'used_at' => null,
        ]);

        // Active token — supplier needs to upload cert for Tomat Pasta (missing)
        SupplierAccessToken::create([
            'company_id' => 1,
            'supplier_id' => 2,
            'ingredient_id' => 17,
            'token' => Str::random(64),
            'expires_at' => $now->copy()->addDays(30),
            'used_at' => null,
        ]);

        // Active token — Minyak Wijen cert expiring soon
        SupplierAccessToken::create([
            'company_id' => 1,
            'supplier_id' => 2,
            'ingredient_id' => 13,
            'token' => Str::random(64),
            'expires_at' => $now->copy()->addDays(20),
            'used_at' => null,
        ]);

        // Already used token
        SupplierAccessToken::create([
            'company_id' => 1,
            'supplier_id' => 4,
            'ingredient_id' => 3,
            'token' => Str::random(64),
            'expires_at' => $now->copy()->addDays(5),
            'used_at' => $now->copy()->subDays(3),
            'ip_address' => '103.28.12.45',
        ]);

        // Expired token
        SupplierAccessToken::create([
            'company_id' => 1,
            'supplier_id' => 6,
            'ingredient_id' => 10,
            'token' => Str::random(64),
            'expires_at' => $now->copy()->subDays(10),
            'used_at' => null,
        ]);
    }
}