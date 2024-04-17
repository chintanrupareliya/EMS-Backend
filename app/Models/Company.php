<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'name',
        'location',
        'company_email',
        'status',
        'website',
        'logo_url',

    ];

    // relationships with other models

    // for delete associated employee , company admin and job
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($company) {
            $company->jobs()->delete();
            $company->users()->deletePasswordResetToken();
            $company->users()->delete();
        });
    }


    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(User::class)->whereIn('type', ['CA', 'E']);
    }

    public function company_admin()
    {
        return $this->hasOne(User::class)->where('type', 'CA');
    }
    
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

}
