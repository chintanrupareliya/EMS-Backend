<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserType;

Route::post('register', [AuthController::class, 'createUser']);
Route::post('login', [AuthController::class, 'loginUser']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUserByToken']);

    Route::middleware([CheckUserType::class . ':SA'])->group(function(){

        Route::get('/stats', [StatsController::class, 'getStats']);

        Route::post('companies/create',[CompanyController::class, 'store']);
        Route::post('companies/update/{id}',[CompanyController::class, 'update']);
        Route::post('companies/delete/{id}', [CompanyController::class, 'destroy']);
        Route::get('companies',[CompanyController::class, 'index']);
        Route::get('companies/{id}',[CompanyController::class, 'show']);

        Route::get('allemployee',[CompanyEmployeeController::class, 'index']);

    });

    Route::middleware([CheckUserType::class . ':SA,CA'])->group(function(){
        Route::post('employee/create',[CompanyEmployeeController::class, 'store']);
        Route::get('employee/{id}',[CompanyEmployeeController::class, 'show']);
        Route::get('companies/options', [CompanyController::class, 'getCompanyOptions']);
        Route::post('employee/update/{id}',[CompanyEmployeeController::class, 'update']);
        Route::post('employee/delete/{id}', [CompanyEmployeeController::class, 'destroy']);
        Route::get('employee/company_emp/{companyId}', [CompanyEmployeeController::class, 'employeesByCompanyId']);
    });

    Route::middleware([CheckUserType::class . ':SA,CA,E'])->group(function(){
        Route::post('job/create',[JobController::class, 'store']);
        Route::get('jobs',[JobController::class, 'index']);
        Route::get('job/{id}',[JobController::class, 'show']);
        Route::get('job/company/{company_id}',[JobController::class, 'jobsByCompanyId']);
        Route::put('job/{id}',[JobController::class, 'update']);
        Route::delete('job/{id}',[JobController::class, 'destroy']);
    });
});

