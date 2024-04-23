<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\CompanyEmployeeController;
use App\Http\Controllers\Api\JobController;
use App\Http\Middleware\CheckUserType;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'createUser']);
    Route::post('login', [AuthController::class, 'loginUser']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/user', [AuthController::class, 'getUserByToken']);
    Route::post('auth/change-password', [AuthController::class, 'changePassword']);

    Route::middleware('check_user_type:SA')->group(function () {
        Route::prefix('companies')->group(function () {
            Route::post('create', [CompanyController::class, 'store']);
            Route::post('update/{id}', [CompanyController::class, 'update']);
            Route::post('delete/{id}', [CompanyController::class, 'destroy']);
            Route::get('/', [CompanyController::class, 'index']);
            Route::get('{id}', [CompanyController::class, 'show']);
        });
    });

    Route::middleware('check_user_type:SA,CA')->group(function () {
        Route::get('stats', [StatsController::class, 'getStats']);

        Route::prefix('employee')->group(function () {
            Route::get('companies/option', [CompanyController::class, 'getCompanyOptions']);
            Route::get('{id}', [CompanyEmployeeController::class, 'show']);
            Route::get('company_emp/{companyId}', [CompanyEmployeeController::class, 'employeesByCompanyId']);
            Route::post('create', [CompanyEmployeeController::class, 'store']);
            Route::post('update/{id}', [CompanyEmployeeController::class, 'update']);
            Route::post('delete/{id}', [CompanyEmployeeController::class, 'destroy']);
            Route::get('/', [CompanyEmployeeController::class, 'index']);
        });

        Route::prefix('job')->group(function () {
            Route::post('create', [JobController::class, 'store']);
            Route::post('update/{id}', [JobController::class, 'update']);
            Route::post('delete/{id}', [JobController::class, 'destroy']);
        });
    });

    Route::get('job/{id}', [JobController::class, 'show']);
    Route::get('jobs/company', [JobController::class, 'jobsByRole']);
});
Route::get('jobs', [JobController::class, 'index']);
