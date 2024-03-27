<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'salary',
        'employment_type',
        'experience_required',
        'skills_required',
        'posted_date',
        'expiry_date',
    ];

    // Define relationships with other models (optional)
    public function company()
    {
        return $this->belongsTo(Company::class); // Job belongs to one company
    }

    public function applications() // Assuming a separate table for applications
    {
        return $this->hasMany(JobApplication::class);
    }
}
