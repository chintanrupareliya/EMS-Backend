<?php

namespace App\Http\Controllers\Api;
use App\Models\Company;
use App\Models\User;
use App\Models\CompanyUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = $this->validate($request, [
            'name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies',
            'website' => 'required|url',
            'status' => 'required|in:A,I',
            'admin.first_name' => 'required|string|max:255',
            'admin.last_name' => 'required|string|max:255',
            'admin.email' => 'required|email|unique:users,email',
            'admin.address' => 'required|string|max:255',
            'admin.city' => 'required|string|max:255',
            'admin.dob' => 'required|date',
            'company_user.joining_date' => 'required|date',
            'company_user.emp_no' => 'required|string|max:255',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        // if ($request->hasFile('logo')) {
        //     $imageName = str_replace(".", "", (string)microtime(true)) . '.' . $request->logo->getClientOriginalExtension();
        //     $request->logo->storeAs("public/logos", $imageName);
        // }
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $path = $logo->store('public/logos');
            $fileName = basename($path);
        }

        $company=Company::create($request->only(['name','company_email','website','location',]+['logo_url'=>$fileName]));

        $password= Hash::make('password');
        // Create a new User record for admin
        $admin = User::create([
            'first_name' =>$request->admin['first_name'] ,
            'last_name'=>$request->admin['last_name'] ,
            'email'=>$request->admin['email'] ,
            'address'=>$request->admin['address'] ,
            'city' =>$request->admin['city'] ,
            'dob'=>$request->admin['dob'] ,
        ]+['password'=>$password, 'type'=>'CA']);

        // Create a new CompanyUser record
        $companyUser = CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $admin->id,
            'joining_date' =>$request->company_user['joining_date'] ,
            'emp_no' => $request->company_user['emp_no'],
        ]);

        return ok('Company created successfully',[], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show($companyId)
    {
        $company = Company::with('admin','companyUsers')->find($companyId);

        if (!$company) {
            return error( 'Company not found', null,'notfound');
        }
        return ok(null,$company, 200);
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
    public function destroy(string $id)
    {

        $company = Company::findOrFail($id);

        $admin = $company->admin;
        $company->companyUsers()->forceDelete();
        $company->delete();

        if ($admin) {
            $admin->delete();
        }

        return ok('Company and associated admin deleted successfully',null, 200);

    }
}
