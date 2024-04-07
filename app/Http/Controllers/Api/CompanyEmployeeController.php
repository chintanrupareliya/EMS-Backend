<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Helpers\EmployeeHelper;
use App\Http\Requests\CreateEmployeeRequest;


require_once app_path('Http/Helpers/APIResponse.php');
class CompanyEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allemployee = User::whereIn('type', ['E', 'CA'])
        ->select('id','company_id', 'first_name', 'last_name', 'email', 'type','emp_no','address','city','dob','salary','joining_date')
        ->get();


    return ok("success",$allemployee,200 );
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

        $user = User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')), // Hash the actual password input
            'type' => 'E',
            "address" => $request->input('address'),
            "city" => $request->input('city'),
            "dob" => $request->input('dob'),
            "salary" => $request->input('salary'),
            "joining_date" => $request->input('joining_date'),
            "emp_no" => EmployeeHelper::generateEmpNo(),
            'company_id' => $request->user()->type === 'CA' ? $request->user()->company_id : $request->input('company_id'),
        ]);

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
    public function update(Request $request, string $id)
    {
       try{ 
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'sometimes|string|email|unique:users,email,' . $id,
                'type' => 'string|in:E',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'dob' => 'nullable|date',
                'salary' => 'nullable|numeric|min:0',
                'joining_date' => 'nullable|date',
                'emp_no' => 'nullable|string|max:255'
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
    public function destroy(string $id)
    {
        $employee = User::findOrFail($id);

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
