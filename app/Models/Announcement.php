<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'message',
        'date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($announcement) {
            $announcement->created_by = Auth::id();
        });

        static::updating(function ($announcement) {
            $announcement->updated_by = Auth::id();
        });

        static::deleting(function ($announcement) {
            $announcement->deleted_by = Auth::id();
            $announcement->save();

        });
    }

}
