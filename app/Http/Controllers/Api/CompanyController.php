<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Models\CompanyUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
require_once app_path('Http/Helpers/APIResponse.php');

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::all();
        return ok(null,$companies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyRequest $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validated();

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $path = $logo->store('public/logos');
            $fileName = basename($path);
        }
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $path = $logo->store('public/logos');
            $fileName = basename($path);
        }

        $companyData = [
            'name' => $validatedData['name'],
            'company_email' => $validatedData['company_email'],
            'website' => $validatedData['website'],
            'location' => $request->input('location'),
            'logo_url' => isset($fileName) ? $fileName : null,
        ];

        $company = Company::create($companyData);
        $adminData = $validatedData['admin'];
        $adminData['company_id'] = $company->id;
        $adminData['password'] = Hash::make('password');
        $adminData['type'] = 'CA';
        // dd($adminData);


        $admin = User::create($adminData);

        // Mail::to($adminData['email'])->send(new InvitationMail($adminData['first_name']));
        return ok('Company created successfully',[$company], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($companyId)
    {
        $company = Company::with('company_admin')->find($companyId);

        if (!$company) {
            return error('Company not found', null, 'notfound');
        }
    
        return ok(null, $company, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate the request data
            $validator = $this->validate($request, [
                'name' => 'string|max:255',
                'company_email' => 'sometimes|email|unique:companies,company_email,' . $id,
                'website' => 'sometimes|url',
                'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
                'location' => 'sometimes|string',
                'status' => 'in:A,I',
                'admin.first_name' => 'sometimes|string|max:255',
                'admin.last_name' => 'sometimes|string|max:255',
                'admin.address' => 'sometimes|string',
                'admin.city' => 'sometimes|string',
                'admin.dob' => 'sometimes|date_format:Y-m-d',
                'company_user.joining_date' => 'sometimes|date_format:Y-m-d',
                'company_user.emp_no' => 'sometimes',
                // Add validation rules for other fields if needed
            ]);

            // Find the company by ID
            $company = Company::findOrFail($id);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $path = $logo->store('public/logos');
                $fileName = basename($path);

                // Delete old logo file if exists
                if ($company->logo_url) {
                    Storage::delete('public/logos/' . $company->logo_url);
                }

                $company->logo_url = $fileName;
            }

            // Update company fields if provided in the request
            $company->fill($request->except(['admin', 'company_user']));
            $company->save();

            // Update related admin user fields if provided in the request
            if ($request->has('admin')) {
                $adminData = $request->input('admin');
                $admin = $company->admin;
                if ($admin) {
                    $admin->update($adminData);
                } else {
                    // Admin user not found for the company
                    return error('Admin user not found',null,'notfound');
                }
            }

            // Update related company_user fields if provided in the request
            if ($request->has('company_user')) {
                $companyUserData = $request->input('company_user');
                $companyUser = $company->companyUsers()->first(); // Assuming only one company user per company
                if ($companyUser) {
                    $companyUser->update($companyUserData);
                } else {
                    // Company user not found for the company
                    return error('Company user not found',null,'notfound');
                }
            }

            // Return success response
            return ok('Company updated successfully', $company);
        } catch (\Exception $e) {
            // Handle any errors
            return  error($e->getMessage(),null);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {

        $company = Company::findOrFail($id);
        $admin = $company->admin;
        $forceDelete = $request->input('forceDelete', false);
        if ($forceDelete) {
            // Permanent deletion
            $company->companyUsers()->forceDelete();
            $company->forceDelete();
        } else {
            // Soft deletion
            $company->companyUsers()->delete();
            $company->delete();
        }

        if ($admin) {
            $admin->delete();
        }

        return ok('Company and associated admin deleted successfully',null, 200);

    }
}
