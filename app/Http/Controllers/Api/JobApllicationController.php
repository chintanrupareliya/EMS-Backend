<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class JobApllicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->type == 'SA') {
                $applications = JobApplication::with('user', 'job')
                    ->select('id', 'resume', 'cover_letter', 'status', 'application_date')
                    ->paginate(10);

            } elseif ($user->type == 'CA') {
                // For company admins, fetch job applications associated with their company's jobs
                $companyJobs = $user->company->jobs()->pluck('id')->toArray();
                $applications = JobApplication::with('user', 'job')
                    ->whereIn('job_id', $companyJobs)
                    ->select('id', 'resume', 'cover_letter', 'status', 'comment', 'application_date')
                    ->paginate(10);
            }

            return ok('success', $applications);
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }

    //controller function for get all job application by user id
    public function getByUser(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $applications = JobApplication::where('user_id', $userId)->get();

            return ok('success', $applications);
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming request data
            $request->validate([
                'job_id' => 'required|exists:jobs,id',
                'resume' => 'required|file|mimes:pdf|max:2048',
                'cover_letter' => 'nullable|string',
            ]);

            $jobApplication = JobApplication::create([
                'user_id' => $request->user()->id,
                'job_id' => $request->job_id,
                'resume' => $request->file('resume')->store('resumes', 'public'),
                'cover_letter' => $request->cover_letter,
            ]);

            return ok("success", $jobApplication);
        } catch (\Exception $e) {
            return error("error", $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            // Find the job application by ID
            $jobApplication = JobApplication::findOrFail($id);

            // Return the job application details
            return ok('success', $jobApplication);
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $jobApplication = JobApplication::findOrFail($id);

            $request->validate([
                'job_id' => 'exists:jobs,id',
                'resume' => 'file|mimes:pdf|max:2048',
                'cover_letter' => 'nullable|string',
                'status' => 'nullable|string',
                'application_date' => 'nullable|date',
            ]);

            $jobApplication->job_id = $request->input('job_id', $jobApplication->job_id);
            $jobApplication->cover_letter = $request->input('cover_letter', $jobApplication->cover_letter);
            $jobApplication->status = $request->input('status', $jobApplication->status);
            $jobApplication->application_date = $request->input('application_date', $jobApplication->application_date);

            if ($request->hasFile('resume')) {
                Storage::disk('public')->delete($jobApplication->resume);

                $jobApplication->resume = $request->file('resume')->store('resumes', 'public');
            }

            $jobApplication->save();

            return ok('success', $jobApplication);
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $jobApplication = JobApplication::findOrFail($id);

            if (!$jobApplication) {
                return error('Job Application not found', [], 'notfound');
            }
            $forceDelete = $request->input('permanent', false);
            if ($forceDelete) {
                Storage::disk('public')->delete($jobApplication->resume);

                $jobApplication->forceDelete();
                return ok('Job Application permanently deleted successfully', []);
            } else {
                $jobApplication->delete();
                return ok('Job Application deleted successfully', []);
            }
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }
}
