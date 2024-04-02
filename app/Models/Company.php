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

    // Define relationships with other models (optional)
    public function employees()
    {
        return $this->hasMany(User::class)->whereIn('type', ['CA', 'E']);
    }

    public function admin()
    {
        return $this->hasOneThrough(User::class, CompanyUser::class, 'company_id', 'id', 'id', 'user_id')
                    ->where('users.type', 'CA');
    }
    public function companyUsers()
    {
        return $this->hasMany(CompanyUser::class);
    }
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

}
