<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CheckUserType;



Route::post('register', [AuthController::class, 'createUser']);
Route::post('login', [AuthController::class, 'loginUser']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'getUserByToken']);
    Route::post('change-password', [AuthController::class, 'changePassword']);


    Route::middleware([CheckUserType::class . ':SA'])->group(function(){
        Route::post('companies/create',[CompanyController::class, 'store']);
        Route::post('companies/update/{id}',[CompanyController::class, 'update']);
        Route::post('companies/delete/{id}', [CompanyController::class, 'destroy']);
        Route::get('companies',[CompanyController::class, 'index']);
        Route::get('companies/{id}',[CompanyController::class, 'show']);
    });

    Route::middleware([CheckUserType::class . ':SA,CA'])->group(function(){

        Route::get('stats', [StatsController::class, 'getStats']);

        Route::get('allemployee',[CompanyEmployeeController::class, 'index']);
        Route::post('employee/create',[CompanyEmployeeController::class, 'store']);
        Route::get('employee/{id}',[CompanyEmployeeController::class, 'show']);
        Route::get('employee/companies/option', [CompanyController::class, 'getCompanyOptions']);
        Route::post('employee/update/{id}',[CompanyEmployeeController::class, 'update']);
        Route::post('employee/delete/{id}', [CompanyEmployeeController::class, 'destroy']);
        Route::get('employee/company_emp/{companyId}', [CompanyEmployeeController::class, 'employeesByCompanyId']);
    });

    Route::middleware([CheckUserType::class . ':SA,CA'])->group(function(){
        Route::post('job/create',[JobController::class, 'store']);
        Route::post('job/update/{id}',[JobController::class, 'update']);
        Route::post('job/delete/{id}',[JobController::class, 'destroy']);
    });

    Route::get('jobs',[JobController::class, 'index']);
    Route::get('job/{id}',[JobController::class, 'show']);
    Route::get('jobs/company',[JobController::class, 'jobsByRole']);
});

