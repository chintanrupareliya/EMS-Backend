<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobs = Job::all();
        return response()->json($jobs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator=$this->validate($request, [
            'company_id' => $request->user()->type === 'SA' ? 'required|exists:companies,id' : 'nullable|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'salary' => 'nullable|numeric',
            'employment_type' => 'nullable|string',
            'required_experience' => 'nullable|string',
            'required_skills' => 'nullable|string',
            'posted_date' => 'nullable|date', 
            'expiry_date' => 'nullable|date',
        ]);
        
       
        
        $validator['company_id'] = $request->user()->type==="SA"? $request->get('company_id') : $request->user()->company_id; 
        $job = Job::create($validator);

        return response()->json($job, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = Job::find($id);
       
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        return response()->json($job);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator=$this->validate($request, [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'salary' => 'sometimes|nullable|numeric',
            'employment_type' => 'sometimes|nullable|string',
            'required_experience' => 'sometimes|nullable|string',
            'required_skills' => 'sometimes|nullable|string',
            'expiry_date' => 'sometimes|nullable|date',
        ]);
    
        
    
        $job = Job::find($id);
    
        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }
    
        $job->update($validator);
    
        return response()->json(['message' => 'Job updated successfully', 'job' => $job], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $job = Job::find($id);

        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }
       
        $job->delete();
    
        return response()->json(['message' => 'Job deleted successfully'], 200);
    }

    public function jobsByCompanyId($companyId)
    {
        $jobs = Job::where('company_id', $companyId)->get();

        if ($jobs->isEmpty()) {
           
            return response()->json(['error' => 'No jobs found for the specified company'], 404);
        }

        return response()->json($jobs);
    }
}
