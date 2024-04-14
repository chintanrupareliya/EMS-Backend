<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $updated_at = false;

    protected $table = "password_reset_tokens";


    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'created_at',
    ];
    protected $dates = [
        'expires_at',
    ];
}
