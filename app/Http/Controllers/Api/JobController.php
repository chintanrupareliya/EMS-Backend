<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Http\Requests\JobRequest;

require_once app_path('Http/Helpers/APIResponse.php');

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $jobs = Job::with(['company' => function ($query) {
                $query->select('id', 'name','logo_url');
            }])->get();
                return ok("success",$jobs);
        }catch (\Exception $e) {
            return error( 'Failed to fetch job data',[], "notfound");
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JobRequest $request)
    {
        try {

            $validator = $request->validate([
                'company_id' => $request->user()->type === 'SA' ? 'required|exists:companies,id' : 'nullable|exists:companies,id',
            ]);
            $userData=$request->only(
            ['title',
            'description',
            'salary',
            'employment_type',
            'required_experience',
            'expiry_date']);
            $userData['company_id'] = $request->user()->type === "SA" ? $request->get('company_id') : $request->user()->company_id;
            if($request->input(['required_skills'])){
                $userData['required_skills'] = json_encode($request->input['required_skills']);
            }

            $job = Job::create($userData);
            return ok("Job created successfully",$job, 201);
        }  catch (\Exception $e) {
            return error('Failed to create job', [ $e->getMessage()] );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = Job::with('company:id,name')->find($id);

        if (!$job) {
            return error( 'Job not found',[], 'notfound');
        }

        return ok('success',$job,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobRequest $request, string $id)
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
            return error('Job not found',[], 'notfound');
        }
        if ($request->user()->type==="SA" || ($request->user()->type==="CA" && $request->user()->company_id === $job->ccompany_id)) {
            $job->update($validator);
            return ok('Job updated successfully', $job);
        }else{
            return error("Unauthorize",[],'unauthenticated');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $job = Job::find($id);

        if (!$job) {
            return response()->json(['error' => 'Job not found'], 404);
        }

        $forceDelete = $request->input('permanent', false);
        if ($forceDelete) {
            $job->forceDelete();
            return response()->json(['message' => 'Job permanently deleted successfully'], 200);
        } else {
            $job->delete();
            return response()->json(['message' => 'Job soft deleted successfully'], 200);
        }
    }

    public function jobsByRole(Request $request)
    {
        $user = $request->user();


        if ($user->type === 'SA') {

            $jobs = Job::with('company:id,name,logo_url')->get();
        } elseif ($user->type === 'CA') {

            $jobs = Job::where('company_id', $user->company_id)->with('company:id,name,logo_url')->get();
        } else {
            return response()->json(['error' => 'Invalid user type'], 400);
        }




        return ok("jobs fetched success",$jobs);
    }
}
