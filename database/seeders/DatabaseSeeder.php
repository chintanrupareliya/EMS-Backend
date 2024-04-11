<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\CompanyUser;
use App\Http\Helpers\EmployeeHelper;

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
        ]);

        $empNo = EmployeeHelper::generateEmpNo();

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'type' => 'CA',
            'address' => 'Admin Address',
            'city' => 'Admin City',
            'dob' => '1990-01-01',
            'company_id' => $company->id,
            'salary' => 50000,
            'joining_date' => now()->format('Y-m-d'),
            'emp_no' => $empNo,
        ]);

    }
}
