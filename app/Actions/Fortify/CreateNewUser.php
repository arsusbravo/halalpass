<?php

namespace App\Actions\Fortify;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            // Company fields
            'company_name' => ['required', 'string', 'max:255'],
            'company_city' => ['required', 'string', 'max:255'],
            'company_province' => ['required', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'company_npwp' => ['nullable', 'string', 'max:20'],

            // User fields
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $company = Company::create([
                'name' => $input['company_name'],
                'city' => $input['company_city'],
                'province' => $input['company_province'],
                'phone' => $input['company_phone'] ?? null,
                'npwp' => $input['company_npwp'] ?? null,
                'status' => 'active',
            ]);

            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'company_id' => $company->id,
                'role' => 'admin',
            ]);
        });
    }
}