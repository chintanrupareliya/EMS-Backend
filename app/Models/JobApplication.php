<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'job_id',
        'resume_path',  // Optional path to uploaded resume
        'cover_letter', // Optional cover letter text
        'application_status', // Status (e.g., 'applied', 'in_review', 'rejected', 'accepted')
    ];

    // Define relationships with other models (optional)
    public function user()
    {
        return $this->belongsTo(User::class); // Application submitted by a user
    }

    public function job()
    {
        return $this->belongsTo(Job::class); // Application for a specific job
    }
}
