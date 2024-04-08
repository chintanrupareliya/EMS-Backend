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
use App\Http\Helpers\EmployeeHelper;

require_once app_path('Http/Helpers/APIResponse.php');

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies =Company::select('id', 'name', 'company_email', 'website', 'location','status','logo_url')
        ->get();
        return ok(null,$companies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyRequest $request)
    {
        try {
        // Validate the incoming request data


        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $path = $logo->store('public/logos');
            $fileName = basename($path);
        }

        $companyData = [
            'name' => $request->input('name'),
            'company_email' => $request->input('company_email'),
            'website' => $request->input('website'),
            'location' => $request->input('location'),
            'logo_url' => isset($fileName) ? $fileName : null,
        ];

        $company = Company::create($companyData);

        $adminData = $request->input('admin');
        $adminData['company_id'] = $company->id;
        $adminData['password'] = Hash::make('password');
        $adminData['type'] = 'CA';
        $adminData['emp_no'] = EmployeeHelper::generateEmpNo();

        $admin = User::create($adminData);

        Mail::to($adminData['email'])->send(new InvitationMail($adminData['first_name'],$adminData['email'],$companyData['name']));
        return ok('Company created successfully',[], 201);
    } catch (Throwable $e) {
        return error('Failed to create company', null, 'internal_server_error');
    }
    }


    /**
     * Display the specified resource.
     */
    public function show($companyId)
    {
        $company = Company::with(['company_admin:id,company_id,first_name,last_name,email,address,city,dob,joining_date,emp_no'])
                          ->select('id', 'name', 'company_email','logo_url','location', 'website', 'status')
                          ->find($companyId);
        if (!$company) {
            return error('Company not found', null, 'notfound');
        }

        // Check if company admin is null
        if (!$company->company_admin) {
            return error('Company admin not found', null, 'notfound');
        }

        return ok(null, $company, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyRequest $request, string $id)
    {
        try {

            // Validate the request data
            $validatedData = $request->validate([
                'company_email' => 'sometimes|email|unique:companies,company_email,' . $id,
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
            $company->fill($request->except(['admin']));
            $company->save();

            // Update related admin user fields if provided in the request
            if ($request->has('admin')) {
                $adminData = $request->input('admin');
                $admin = $company->company_admin;
                if ($admin) {
                    $admin->update($adminData);
                } else {
                    // Admin user not found for the company
                    return error('Admin user not found', null, 'notfound');
                }
            }
            // Return success response
            return ok('Company updated successfully', $company);
        } catch (\Exception $e) {
            // Handle any errors
            return error($e->getMessage(), null);
        }
    }
    public function getCompanyOptions()
    {
        $companies = Company::select('id', 'name')->get();
        return response()->json($companies);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $company = Company::with('company_admin')->findOrFail($id);

            $forceDelete = $request->input('forceDelete', false);

            // If forceDelete is true, delete the company and its admin permanently
            if ($forceDelete) {
                // Check if the company admin exists
                if ($company->company_admin) {
                    $company->company_admin->forceDelete();
                }

                $company->forceDelete();
            } else {
                // Soft delete the company and its admin
                if ($company->company_admin) {
                    $company->company_admin->delete();
                }

                $company->delete();
            }

            return ok('Company and associated admin deleted successfully', null, 200);
        } catch (\Exception $e) {
            // Handle errors
            return error('Error deleting company and associated admin: ' . $e->getMessage(), null, 'error');
        }
    }
}
