<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Company extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'location',
        'company_email',
        'status',
        'website',
        'logo_url',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // relationships with other models

    // for delete associated employee , company admin and job
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($company) {
            $company->created_by = Auth::id();
        });

        static::updating(function ($company) {
            $company->updated_by = Auth::id();
        });

        static::deleting(function ($company) {
            $company->deleted_by = Auth::id();
            $company->save();
            $company->jobs()->delete();
            $company->users->each(function ($user) {
                $user->deletePasswordResetToken();
            });
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
