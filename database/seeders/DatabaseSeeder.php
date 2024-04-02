<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\CompanyUser;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('password'),
            'type' => 'SA',
        ]);
        $company = Company::create([
            'name' => 'Example Company',
            'location' => 'Location',
            'company_email' => 'company@example.com',
            'status' => 'A',
            'website' => 'https://example.com',
            'logo_url' => 'https://example.com/logo.png',
        ]);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => 'CA',
            'address' => 'Admin Address',
            'city' => 'Admin City',
            'dob' => '1990-01-01',
        ]);

        // Create a record in the company_user table
        $companyUser = CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'salary' => 50000.00, // Sample salary
            'joining_date' => '2024-01-01', // Sample joining date
            'emp_no' => 'EMP001', // Sample employee number
        ]);
    }
}
