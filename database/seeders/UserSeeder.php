<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Platform owner — no fixed company, uses company switcher
        User::create([
            'name' => 'Ario',
            'email' => 'info@arsus.nl',
            'password' => Hash::make('arsus@29'),
            'company_id' => null,
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);
    }
}