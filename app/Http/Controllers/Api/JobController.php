<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Http\Requests\JobRequest;


class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $jobs = Job::with([
                'company' => function ($query) {
                    $query->select('id', 'name', 'logo_url', 'location');
                }
            ])->get();
            return ok("success", $jobs);
        } catch (\Exception $e) {
            return error('Failed to fetch job data', [], "notfound");
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
            $userData = $request->only(
                [
                    'title',
                    'description',
                    'salary',
                    'employment_type',
                    'required_skills',
                    'required_experience',
                    'expiry_date'
                ]
            );
            $userData['company_id'] = $request->user()->type === "SA" ? $request->get('company_id') : $request->user()->company_id;

            $job = Job::create($userData);
            return ok("Job created successfully", $job, 201);
        } catch (\Exception $e) {
            return error('Failed to create job', [$e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = Job::with('company:id,name')->find($id);

        if (!$job) {
            return error('Job not found', [], 'notfound');
        }

        return ok('success', $job, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobRequest $request, string $id)
    {

        $job = Job::find($id);

        if (!$job) {
            return error('Job not found', [], 'notfound');
        }
        if ($request->user()->type === "SA" || ($request->user()->type === "CA" && $request->user()->company_id === $job->company_id)) {
            $job->update($request->all());
            return ok('Job updated successfully', $job);
        } else {
            return error("Unauthorize", [], 'unauthenticated');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $job = Job::find($id);

        if (!$job) {
            return error('Job not found', [], 'notfound');
        }

        $forceDelete = $request->input('permanent', false);
        if ($forceDelete) {
            $job->forceDelete();
            return ok('Job permanently deleted successfully', []);
        } else {
            $job->delete();
            return ok('Job soft deleted successfully', []);
        }
    }

    public function jobsByRole(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10); // Number of items per page

            if ($user->type === 'SA') {
                $query = Job::with('company:id,name,logo_url');
            } elseif ($user->type === 'CA') {
                $query = Job::where('company_id', $user->company_id)->with('company:id,name,logo_url');
            } else {
                return error('Invalid user type', [], 'notfound');
            }

            // Apply search filter if search parameter is present
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%");
                });
            }

            if ($request->has('filter')) {
                $filter = $request->input('filter');
                $query->where('employment_type', $filter);
            }

            $jobs = $query->paginate($perPage);

            return ok('success', $jobs, 200);
        } catch (\Exception $e) {

            return error(['error' => 'Failed to fetch jobs data'], 500);
        }
    }
}
