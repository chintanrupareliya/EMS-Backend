<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use App\Http\Helpers\EmployeeHelper;
use App\Http\Requests\CreateEmployeeRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmployeeInvitationMail;

require_once app_path('Http/Helpers/APIResponse.php');

class CompanyEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    try {

        $user = $request->user();


        $employees = User::where(function ($query) use ($user) {
            if ($user->type === 'CA') {
                $query->where('type', 'E')
                      ->where('company_id', $user->company_id);
            }
            elseif ($user->type === 'SA') {
                $query->where('type', 'E')
                ->orWhere('type', 'CA');
            }
        })
        ->with(['company:id,name'])
        ->select(
            'id',
            'company_id',
            'first_name',
            'last_name',
            'email',
            'type',
            'emp_no',
            'address',
            'city',
            'dob',
            'salary',
            'joining_date'
        )
        ->get();

        // Return the response
        return ok('success', $employees, 200);
    } catch (\Exception $e) {
        // Return error response if an exception occurs
        return response()->json(['error' => 'Failed to fetch employee data'], 500);
    }
}
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateEmployeeRequest $request)
    {
        if($request->user()->type === 'SA'){
            $validated = $request->validate([
              "company_id" => "required|exists:companies,id",
            ]);
        }

        $company = Company::withTrashed()->findOrFail($request->input('company_id'));

        // Check if the company is soft-deleted
        if ($company->trashed()) {
            return response()->json(['error' => 'Cannot create employee for a deleted company'], 400);
        }

        $user = User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'type' => 'E',
            "address" => $request->input('address'),
            "city" => $request->input('city'),
            "dob" => $request->input('dob'),
            "salary" => $request->input('salary'),
            "joining_date" => $request->input('joining_date'),
            "emp_no" => EmployeeHelper::generateEmpNo(),
            'company_id' => $request->user()->type === 'CA' ? $request->user()->company_id : $request->input('company_id'),
        ]);
        $company = Company::findOrFail($user['company_id']);

        $token = Str::random(60);

        PasswordReset::create([
            'email' => $user['email'],
            'token' => $token,
            'expires_at' => now()->addMinutes(30),
        ]);

        $resetLink= config('constant.frontend_url') . config('constant.reset_password_url') . $token;

        Mail::to($user['email'])->send(new EmployeeInvitationMail($user['first_name'],$user['email'],$company['name'],$resetLink));
        return ok("user created successfully",$user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
       try{
        $employee = User::where('id', $id)->whereIn('type', ['E', 'CA'])->first();

        if (auth()->user()->type === 'CA') {

            if ($employee->company_id !== auth()->user()->company_id) {
                return error( '',[], 'forbidden');
            }
        }

        elseif (auth()->user()->type !== 'SA') {
            return error('Unauthorized. Only company admins (CA) and super admins (SA) can view employees.',[], 'unauthenticated');
        }

        if ($employee->type !== 'E' && $employee->type !== 'CA') {
            return error('requested user is not Employee', [], 'notfound');
        }

        return ok('success',$employee,200);
    }catch (\Exception $e) {
        return error('Employee not found.', [], 'not_found');
    }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CreateEmployeeRequest $request, string $id)
    {
       try{
            $validatedData = $request->validate([
                'email' => 'sometimes|string|email|unique:users,email,' . $id,   
            ]);

            $employee = User::where('id', $id)->whereIn('type', ['E', 'CA'])->first();
            if (!$employee) {
                return response()->json(['error' => 'Employee not found'], 404);
            }
            if ($request->user()->type === 'CA') {

                if ($employee->company_id !== auth()->user()->company_id) {
                    return error( '',[], 'forbidden');
                }
            }
            $employee->update($request->all());


            return response()->json(['message' => 'Employee updated successfully', 'employee' => $employee], 200);
        } catch (\Exception $e) {
             return response()->json(['error' => 'Employee not found'], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
       try
       {
            $employee = User::where('id', $id)->whereIn('type', ['E', 'CA'])->first();

            $forceDelete = $request->input('permanent', false);
            if ($forceDelete ) {
                $employee->forceDelete();
                return response()->json(['message' => 'Employee permanently deleted successfully'], 200);
            } else {
                $employee->delete();
                return response()->json(['message' => 'Employee deleted successfully'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

    }

    //get all employee of particular company
    public function employeesByCompanyId($companyId)
    {
        try {
            $company = Company::findOrFail($companyId);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid company ID'], 404);
        }

        try {
            $employees = User::where('company_id', $companyId)->whereIn('type', ['E', 'CA'])->get();

            if ($employees->isEmpty()) {
                return response()->json(['error' => 'No employees found for this company'], 404);
            }

            return response()->json($employees);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
