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
        $validator=$this->validate($request,[
            'name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:companies',
            'website' => 'required|url',
            'logo_url' => 'nullable|url',
            'admin_first_name'=>'required|string|max:255',
            'admin_last_name'=>'required|string|max:255',
            'admin_email'=>'required|email|unique:users,email',

        ]);

        $company = Company::create([
            'name'=> $validator['name'],
            'company_email'=>$validator['company_email'],
            'website'=> $validator['website'],
            'location'=>$request->get('location'),
            'logo_url'=>$request->get('logo_url')
        ]);

        $user = User::create([
            'first_name' => $validator['admin_first_name'],
            'last_name' => $validator['admin_last_name'],
            'email' => $validator['admin_email'],
            'type' => 'CA',
            'password' => Hash::make('password'), // Set default password here
        ]);

        $employee=CompanyUser::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'company' => $company,
            'user' => $user,
            'company employee'=>$employee
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($companyId)
{
    $company = Company::with('admin')->find($companyId);

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
        $validator=$this->validate($request, [
            'name' => 'sometimes|string|max:255',
            'company_email' => 'sometimes|email|unique:companies,company_email,' . $id,
            'website' => 'sometimes|url',
            'logo_url' => 'nullable|url',
        ]);

        $company = Company::findOrFail($id);

        // Update company fields if provided in the request
        $company->fill($validator);
        $company->save();
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
