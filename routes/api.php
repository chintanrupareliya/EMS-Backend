<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserType;

Route::post('register', [AuthController::class, 'createUser']);
Route::post('login', [AuthController::class, 'loginUser']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    
    Route::middleware([CheckUserType::class . ':SA'])->group(function(){
        Route::post('companies/create',[CompanyController::class, 'store']);
        Route::get('companies',[CompanyController::class, 'index']);
        Route::get('companies/{id}',[CompanyController::class, 'show']);
        Route::get('companies/all_employees',[CompanyEmployeeController::class, 'index']);
        
    });

    Route::middleware([CheckUserType::class . ':SA,CA,E'])->group(function(){
      
    });
    
});

