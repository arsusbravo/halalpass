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
            'password' => Hash::make('test1234'),
            'company_id' => null,
            'role' => 'owner',
            'email_verified_at' => now(),
        ]);

        // Company 1 admin
        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@berkahpangan.co.id',
            'password' => Hash::make('test1234'),
            'company_id' => 1,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Company 1 manager
        User::create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti@berkahpangan.co.id',
            'password' => Hash::make('test1234'),
            'company_id' => 1,
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);

        // Company 1 viewer
        User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad@berkahpangan.co.id',
            'password' => Hash::make('test1234'),
            'company_id' => 1,
            'role' => 'viewer',
            'email_verified_at' => now(),
        ]);

        // Company 2 admin
        User::create([
            'name' => 'Dewi Lestari',
            'email' => 'dewi@sarirasa.co.id',
            'password' => Hash::make('test1234'),
            'company_id' => 2,
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}