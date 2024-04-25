<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'job_id',
        'resume',
        'cover_letter',
        'status',
        'application_date',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = ['application_date', 'deleted_at'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job_application) {
            $job_application->created_by = Auth::id();
        });

        static::updating(function ($job_application) {
            $job_application->updated_by = Auth::id();
        });

        static::deleting(function ($job_application) {
            $job_application->deleted_by = Auth::id();
            $job_application->save();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
