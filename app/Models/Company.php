<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'location',
        'email',
        'contact_number',
        'website',
        'logo_url',
    ];

    // Define relationships with other models (optional)
    public function employees() 
    {
        return $this->hasMany(User::class)->where('type', 'E'); 
    }

    public function jobs()
    {
        return $this->hasMany(Job::class); 
    }
    public function companyAdmin()
    {
        return $this->hasOne(User::class, 'company_id')->where('type', 'CA'); // Filter for company admin
    }
}
