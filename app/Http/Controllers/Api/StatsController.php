<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Job;

class StatsController extends Controller
{
    public function getStats(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Initialize variables
        $userCount = User::count();
        $employeeCount = null;
        $companyCount = null;
        $jobCount = null;

        // For super admin, get all statistics
        if ($user->type === 'SA') {
            $employeeCount = User::whereIn('type', ['E', 'CA'])->count();
            $companyCount = Company::count();
            $jobCount = Job::count();
        }
        // For company admin, get statistics related to their company
        elseif ($user->type === 'CA') {
            $companyId = $user->company_id;
            $employeeCount = User::where('company_id', $companyId)->whereIn('type', ['E'])->count();
            $companyCount = 1; // Company admin can only see their own company
            $jobCount = Job::where('company_id', $companyId)->count();
        }

        return response()->json([
            'user_count' => $userCount,
            'employee_count' => $employeeCount,
            'company_count' => $companyCount,
            'job_count' => $jobCount
        ]);
    }
}
