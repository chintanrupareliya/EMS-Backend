<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
class CompanyEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allemployee = User::whereIn('type', ['E', 'CA'])
        ->get();

    return response()->json($allemployee );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {  
        $validator=Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'type' => 'string|in:E',
            'company_id' => [ 
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->user()->type === 'CA') {
                        if ($value !== $request->user()->company_id) {
                            $fail('Company admin can only create employees for their own company.');
                        }
                    } else if ($request->user()->type !== 'SA') {
                        $fail('Unauthorized to create employees.');
                    }
                }, 
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make("password"),
            'type' => 'E',
            'company_id' => $request->user()->type === 'CA' ? $request->user()->company_id : $validatedData['company_id'],
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = User::findOrFail($id);
        if ($employee->type !== 'E' && $employee->type !== 'CA') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($employee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $id,
            'type' => 'sometimes|string|in:E',
            'company_id' => [ 
                'sometimes',
                'exists:companies,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->user()->type === 'CA') {
                        if ($value !== $request->user()->company_id) {
                            $fail('Company admin can only update employees for their own company.');
                        }
                    } else if ($request->user()->type !== 'SA') {
                        $fail('Unauthorized to update employees.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $employee = User::find($id);;
        if (!$employee) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $employee->fill($validator->validated());
        $employee->save();

        return response()->json(['message' => 'Employee updated successfully', 'employee' => $employee], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee = User::find($id);;
        if (!$employee) {
            return response()->json(['error' => 'User not found'], 404);
        }
        if ($employee->type !== 'E' && $employee->type !== 'CA') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $employee->delete();
        return response()->json(['message' => 'Employee deleted successfully'], 200);
    }

    //get all employee of particular company
    public function employeesByCompanyId($companyId)
    {
        $employees = User::where('company_id', $companyId)->whereIn('type', ['E', 'CA'])->get();

        return response()->json($employees);
    }
}
