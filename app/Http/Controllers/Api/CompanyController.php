<?php

namespace App\Http\Controllers\Api;
use App\Models\Company;
use App\Models\User;
use App\Models\CompanyUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::all();
        return response()->json($companies, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validate the incoming request data
    $validator = $this->validate($request, [
        'name' => 'required|string|max:255',
        'company_email' => 'required|email|unique:companies',
        'website' => 'required|url',
        'logo_url' => 'nullable|url',
        'status' => 'required|in:A,I',
        'admin.first_name' => 'required|string|max:255',
        'admin.last_name' => 'required|string|max:255',
        'admin.email' => 'required|email|unique:users,email',
        'admin.address' => 'required|string|max:255',
        'admin.city' => 'required|string|max:255',
        'admin.dob' => 'required|date',
        'company_user.joining_date' => 'required|date',
        'company_user.emp_no' => 'required|string|max:255',
    ]);

    // Create a new Company record
    $company = Company::create([
        'name' => $validator['name'],
        'company_email' => $validator['company_email'],
        'website' => $validator['website'],
        'location' => $request->get('location'),
        'logo_url' => $validator['logo_url'],
    ]);

    // Create a new User record for admin
    $admin = User::create([
        'first_name' => $validator['admin']['first_name'],
        'last_name' => $validator['admin']['last_name'],
        'email' => $validator['admin']['email'],
        'type' => 'CA',
        'password' => Hash::make('password'), // Set default password here
        'address' => $validator['admin']['address'],
        'city' => $validator['admin']['city'],
        'dob' => $validator['admin']['dob'],
    ]);

    // Create a new CompanyUser record
    $companyUser = CompanyUser::create([
        'company_id' => $company->id,
        'user_id' => $admin->id,
        'joining_date' => $validator['company_user']['joining_date'],
        'emp_no' => $validator['company_user']['emp_no'],
    ]);

    return response()->json(['message' => 'Company created successfully',
        'company' => $company,
        'admin' => $admin,
        'company_user' => $companyUser,
    ], 201);
}


    /**
     * Display the specified resource.
     */
    public function show($companyId)
    {
        $company = Company::with('admin','companyUser')->find($companyId);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }
        return response()->json($company, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
{
    $validator = $this->validate($request, [
        'name' => 'sometimes|string|max:255',
        'company_email' => 'sometimes|email|unique:companies,company_email,' . $id,
        'website' => 'sometimes|url',
        'logo_url' => 'nullable|url',
        'location' => 'sometimes|string', // Add location validation if needed
        'status' => 'required|in:A,I',
        'admin.first_name' => 'sometimes|string|max:255',
        'admin.last_name' => 'sometimes|string|max:255',
        'admin.address' => 'sometimes|string', // Add address validation if needed
        'admin.city' => 'sometimes|string', // Add city validation if needed
        'admin.dob' => 'sometimes|date_format:Y-m-d', // Add date format validation if needed
        'company_user.joining_date' => 'sometimes|date_format:Y-m-d', // Add date format validation if needed
        'company_user.emp_no' => 'sometimes', // Add validation rules for emp_no if needed
        // Add validation rules for other fields if needed
    ]);

    $company = Company::findOrFail($id);

    // Update company fields if provided in the request
    $company->fill($validator);
    $company->save();

    // Update related admin user fields if provided in the request
    if ($request->has('admin')) {
        $adminData = $validator['admin'];
        $admin = $company->admin;
        if ($admin) {
            $admin->update($adminData);
        } else {
            // Admin user not found for the company
            return response()->json(['error' => 'Admin user not found'], 404);
        }
    }

    // Update related company_user fields if provided in the request
    if ($request->has('company_user')) {
        $companyUserData = $validator['company_user'];
        $companyUser = $company->companyUser()->first(); // Assuming only one company user per company
        if ($companyUser) {
            $companyUser->update($companyUserData);
        } else {
            // Company user not found for the company
            return response()->json(['error' => 'Company user not found'], 404);
        }
    }

    return response()->json(['message' => 'Company updated successfully', 'company' => $company], 200);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully'], 200);

    }
}
