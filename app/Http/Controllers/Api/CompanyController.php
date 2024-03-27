<?php

namespace App\Http\Controllers\Api;
use App\Models\Company;
use App\Models\User;
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
        $validation=$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies',
            'website' => 'required|url',
            'logo_url' => 'nullable|url',
            'admin_name'=>'required|string|max:255',
            'admin_email'=>'required|email',
        ]);

        $company = Company::create([
            'name'=> $validation['name'],
            'email'=> $validation['email'],
            'website'=>  $validation['website'],
        ]);

        $user = User::create([
            'name' => $validation['admin_name'],
            'email' => $validation['admin_email'],
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
