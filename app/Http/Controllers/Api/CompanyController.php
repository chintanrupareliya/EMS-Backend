<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Models\User;
use App\Models\CompanyUser;
use App\Models\PasswordReset;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use App\Http\Helpers\EmployeeHelper;



class CompanyController extends Controller
{


    /**
     * Display a listing of the companies.
     *
     * @method GET
     * @route /companies
     * @authentication yes
     * @middleware none
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Company::select('id', 'name', 'company_email', 'website', 'location', 'status', 'logo_url');

        //for searching
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('company_email', 'like', "%$search%")
                    ->orWhere('website', 'like', "%$search%")
                    ->orWhere('location', 'like', "%$search%");
            });
        }

        //for filter based on company status
        if ($request->has('filter')) {
            $filter = $request->input('filter');
            $query->where('status', $filter);
        }

        $perPage = $request->input('per_page', 10);
        $companies = $query->paginate($perPage);

        return ok("success", $companies);
    }


    /**
     * Store a newly created company in storage with company admin.
     *
     * @method POST
     * @route /companies/create
     * @authentication yes
     * @middleware none
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created company in storage with company admin.
     */
    public function store(CompanyRequest $request)
    {
        try {

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $path = $logo->store('public/logos');
                $fileName = basename($path);
            }

            //store company
            $companyData = [
                'name' => $request->input('name'),
                'company_email' => $request->input('company_email'),
                'website' => $request->input('website'),
                'location' => $request->input('location'),
                'logo_url' => isset($fileName) ? $fileName : null,
            ];

            $company = Company::create($companyData);

            //store company admin in user table with type CA
            $adminData = $request->input('admin');
            $adminData['company_id'] = $company->id;
            $adminData['password'] = Hash::make('password');
            $adminData['type'] = 'CA';
            $adminData['emp_no'] = EmployeeHelper::generateEmpNo();

            $admin = $company->users()->create($adminData);


            //it will generate a reset password token and send it to newly created company admin for reset their password
            $token = Str::random(60);

            PasswordReset::create([
                'email' => $adminData['email'],
                'token' => $token,
            ]);

            $resetLink = config('constant.frontend_url') . config('constant.reset_password_url') . $token;

            Mail::to($adminData['email'])->send(new InvitationMail($adminData['first_name'], $adminData['email'], $companyData['name'], $resetLink));
            return ok('Company created successfully', [], 201);
        } catch (\Exception $e) {
            return error('Failed to create company', null, 'internal_server_error');
        }
    }


    /**
     * Display the specified company.
     *
     * @method GET
     * @route /companies/{companyId}
     * @authentication yes
     * @middleware none
     * @param string $companyId
     * @return \Illuminate\Http\Response
     */

    /**
     * Display the specified resource.
     */
    public function show($companyId)
    {
        $company = Company::with(['company_admin:id,company_id,first_name,last_name,email,address,city,dob,joining_date,emp_no'])
            ->select('id', 'name', 'company_email', 'logo_url', 'location', 'website', 'status')
            ->findOrFail($companyId);
        if (!$company) {
            return error('Company not found', null, 'notfound');
        }

        // Check if company admin is null
        if (!$company->company_admin) {
            return error('Company admin not found', null, 'notfound');
        }

        return ok("success", $company, 200);
    }

    /**
     * Update the specified company in storage.
     *
     * @method POST
     * @route /companies/update/{id}
     * @authentication yes
     * @middleware none
     * @param string $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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

    /**
     * Get options for companies based on user type.
     *
     * @method GET
     * @route /employee/companies/option
     * @authentication yes
     * @middleware none
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    //for gating  company name and id of all company
    public function getCompanyOptions(Request $request)
    {
        // Check the user type
        $userType = $request->user()->type;

        // If the user type is 'CA' (Company Admin), only return the company associated with the user
        if ($userType === 'CA') {
            $companyId = $request->user()->company_id;
            $companies = Company::where('id', $companyId)->select('id', 'name')->get();
        } else {
            // For other user types, return all companies
            $companies = Company::select('id', 'name')->get();
        }

        return ok("success", $companies, 200);
    }

    /**
     * Delete the specified company and its associated admin.
     *
     * @method POST
     * @route /companies/delete/{id}
     * @authentication yes
     * @middleware none
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $company = Company::with('company_admin')->findOrFail($id);

            $forceDelete = $request->input('forceDelete', false);

            // If forceDelete is true, delete the company and its admin permanently
            if ($forceDelete) {
                // Delete the company logo
                if ($company->logo_url) {
                    Storage::delete('public/logos/' . $company->logo_url);
                }

                $company->forceDelete();

            } else {
                $company->delete();
            }

            return ok('Company and associated admin deleted successfully', [], 200);
        } catch (\Exception $e) {
            // Handle errors
            return error('Error deleting company and associated admin: ' . $e->getMessage(), null, 'error');
        }
    }
}
