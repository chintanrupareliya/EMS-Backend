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
     * Display a listing of the job applications.
     *
     * @method GET
     * @route /job_application
     * @authentication yes
     * @middleware none
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);

            if (!$user) {
                throw new \Exception("User not authenticated");
            }

            if ($user->type == 'SA') {
                $query = JobApplication::with('user:id,first_name,last_name,email,type,dob', 'job:id,company_id,title,description,salary,required_experience,required_skills');


            } elseif ($user->type == 'CA') {

                $companyJobs = $user->company->jobs()->pluck('id')->toArray();
                $query = JobApplication::with('user:id,first_name,last_name,email,type,dob', 'job:id,company_id,title,description,salary,required_experience,required_skills');

            } else {
                return error("Invalid user type", [], 'notfound');
            }
            $applications = $query->select('id', 'user_id', 'job_id', 'resume', 'cover_letter', 'status', 'application_date')->paginate($perPage);
            return ok('success', $applications);
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }

    /**
     * Get all job applications by user ID.
     *
     * @method GET
     * @route /job_applications/my_application
     * @authentication yes
     * @middleware none
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getByUser(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $applications = JobApplication::with('job:id,company_id,title,description,salary,employment_type,required_experience,required_skills', 'job.company:id,name,logo_url,location')->select('id', 'user_id', 'job_id', 'resume', 'cover_letter', 'status', 'application_date')->where('user_id', $userId)->get();

            return ok('success', $applications);
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }


    /**
     * Store a newly created job application in storage.
     *
     * @method POST
     * @route /job_application/create
     * @authentication yes
     * @middleware none
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
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
            $existingApplication = JobApplication::where('user_id', $request->user()->id)
                ->where('job_id', $request->job_id)
                ->first();

            if ($existingApplication) {
                throw new \Exception('You have already applied for this job.');
            }

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
     * Display the specified job application.
     *
     * @method GET
     * @route job_application/show/{id}
     * @authentication yes
     * @middleware none
     * @param string $id The ID of the job application to retrieve
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        try {
            // Find the job application by ID
            $jobApplication = JobApplication::with('user:id,first_name,last_name', 'job:id,title')->select('id', 'user_id', 'job_id', 'resume', 'cover_letter', 'status', 'application_date')
                ->findOrFail($id);

            // Return the job application details
            return ok('success', $jobApplication);
        } catch (\Exception $e) {
            return error('error', $e->getMessage(), "notfound");
        }
    }

    /**
     * Update the specified job application.
     *
     * @method POST
     * @route /job_application/update/{id}
     * @authentication yes
     * @middleware none
     * @param Request $request The HTTP request object
     * @param string $id The ID of the job application to update
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        try {
            $jobApplication = JobApplication::findOrFail($id);


            $request->validate([
                'job_id' => 'exists:jobs,id',
                'resume' => 'nullable|file|mimes:pdf|max:2048',
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
     * Delete the specified job application.
     *
     * @method POST
     * @route /job_application/delete/{id}
     * @authentication yes
     * @middleware none
     * @param Request $request The HTTP request object
     * @param string $id The ID of the job application to delete
     * @return \Illuminate\Http\Response
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
