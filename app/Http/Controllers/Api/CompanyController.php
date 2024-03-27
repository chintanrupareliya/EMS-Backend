<?php

namespace App\Http\Controllers\Api;
use App\Models\Company;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
        $validator=Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies',
            'website' => 'required|url',
            'logo_url' => 'nullable|url',
            'admin_first_name'=>'required|string|max:255',
            'admin_last_name'=>'required|string|max:255',
            'admin_email'=>'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();
        $company = Company::create([
            'name'=> $validatedData['name'],
            'email'=>$validatedData['email'],
            'website'=> $validatedData['website'],
        ]);

        $user = User::create([
            'first_name' => $validatedData['admin_first_name'],
            'last_name' => $validatedData['admin_last_name'],
            'email' => $validatedData['admin_email'],
            'type' => 'CA', 
            'password' => Hash::make('password'), // Set default password here
            'company_id' => $company->id 
        ]);
        
        return response()->json([
            'company' => $company,
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $company = Company::findOrFail($id);
        return response()->json($company, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:companies,email,' . $id,
            'website' => 'sometimes|url',
            'logo_url' => 'nullable|url',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $company = Company::findOrFail($id);
    
        // Update company fields if provided in the request
        $company->fill($validator->validated());
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
