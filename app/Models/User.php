<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'type',
        'address',
        'city',
        'dob',
        'salary',
        'joining_date',
        'emp_no',
        'company_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->created_by = Auth::id();
        });

        static::updating(function ($user) {
            $user->updated_by = Auth::id();
        });

        static::deleting(function ($user) {
            $user->deleted_by = Auth::id();
            $user->save();
        });
    }

    //relationship with other models
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function deletePasswordResetToken()
    {
        $passwordResetToken = PasswordReset::where('email', $this->email)->first();

        if ($passwordResetToken) {
            $passwordResetToken->delete();
        }
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
