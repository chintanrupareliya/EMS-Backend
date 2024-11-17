<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\User;
use App\Models\Job;
use App\Http\Helpers\EmployeeHelper;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the Super Admin
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'type' => 'SA',
        ]);

        // Seed multiple companies
        for ($i = 1; $i <= 5; $i++) {
            $company = Company::create([
                'name' => "Company $i",
                'location' => 'City ' . $i,
                'company_email' => "company$i@example.com",
                'status' => 'A',
                'website' => "https://company$i.com",
            ]);

            // Seed an admin user for each company
            $empNo = EmployeeHelper::generateEmpNo();
            User::create([
                'first_name' => "Admin $i",
                'last_name' => 'Admin',
                'email' => "admin$i@company.com",
                'password' => Hash::make('password'),
                'type' => 'CA',
                'address' => "Admin Address $i",
                'city' => "City $i",
                'dob' => '1990-01-01',
                'company_id' => $company->id,
                'salary' => rand(40000, 60000),
                'joining_date' => now()->subDays(rand(0, 365))->format('Y-m-d'),
                'emp_no' => $empNo,
            ]);

            // Seed multiple employees for each company
            for ($j = 1; $j <= 10; $j++) {
                $empNo = EmployeeHelper::generateEmpNo();
                User::create([
                    'first_name' => "Employee $j",
                    'last_name' => "LastName $j",
                    'email' => "employee$j.company$i@example.com",
                    'password' => Hash::make('password'),
                    'type' => 'E',
                    'address' => "Employee Address $j",
                    'city' => "City $i",
                    'dob' => '1995-01-01',
                    'company_id' => $company->id,
                    'salary' => rand(30000, 50000),
                    'joining_date' => now()->subDays(rand(0, 365))->format('Y-m-d'),
                    'emp_no' => $empNo,
                ]);
            }

            // Seed jobs for each company
            Job::factory(10)->create(['company_id' => $company->id]);
        }
    }
}