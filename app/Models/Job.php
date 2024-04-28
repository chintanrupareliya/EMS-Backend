<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $casts = [
        'required_experience' => 'array',
        'required_skills' => 'array',
    ];

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'salary',
        'employment_type',
        'required_experience',
        'required_skills',
        'posted_date',
        'expiry_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    //relationship with other models
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            $job->created_by = Auth::id();
        });

        static::updating(function ($job) {
            $job->updated_by = Auth::id();
        });

        static::deleting(function ($job) {
            $job->deleted_by = Auth::id();
            $job->save();
        });
    }




    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }
}
