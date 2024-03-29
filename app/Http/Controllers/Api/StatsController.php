<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Job;

class StatsController extends Controller
{
    public function getStats()
{
    $userCount = User::count();
    $employeeCount = User::where('type', 'E')->orWhere('type', 'CA')->count();
    $companyCount = Company::count();
    $jobCount = Job::count();

    return response()->json([
        'user_count' => $userCount,
        'employee_count' => $employeeCount,
        'company_count' => $companyCount,
        'job_count' => $jobCount
    ]);
}
}
