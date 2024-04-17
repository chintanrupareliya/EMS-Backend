<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


//not yet implemented
class JobApplication extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'job_id',
        'resume_path',
        'cover_letter',
        'application_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
