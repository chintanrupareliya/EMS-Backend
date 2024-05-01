<?php

use App\Http\Controllers\Api\JobApllicationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\StatsController;
use App\Http\Controllers\Api\CompanyEmployeeController;
use App\Http\Controllers\Api\JobController;

Route::prefix('auth')->controller(AuthController::class)->group(function () {
    // Authentication routes
    Route::post('register', 'createUser');
    Route::post('login', 'loginUser');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('reset-password', 'resetPassword');
});

Route::middleware('auth:sanctum')->group(function () {
    // Authenticated routes
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout');
        Route::get('user', 'getUserByToken');
        Route::post('change-password', 'changePassword');
    });

    Route::middleware('check_user_type:SA')->group(function () {
        // Routes accessible only to Super Admins (SA)
        Route::prefix('companies')->controller(CompanyController::class)->group(function () {
            // Company routes
            Route::post('create', 'store');
            Route::post('update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
            Route::get('/', 'index');
            Route::get('{id}', 'show');
        });
    });

    Route::middleware('check_user_type:SA,CA')->group(function () {
        // Routes accessible to Super Admins (SA) and Company Admins (CA)
        Route::get('stats', [StatsController::class, 'getStats']);

        Route::get('employee/companies/option', [CompanyController::class, 'getCompanyOptions']);

        Route::prefix('employee')->controller(CompanyEmployeeController::class)->group(function () {
            // Employee routes
            Route::get('{id}', 'show');
            Route::get('company_emp/{companyId}', 'employeesByCompanyId');
            Route::post('create', 'store');
            Route::post('update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
            Route::get('/', 'index');
        });

        Route::prefix('job')->controller(JobController::class)->group(function () {
            // Job routes
            Route::post('create', 'store');
            Route::post('update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
            Route::get('company', 'jobsByRole');
            Route::get('{id}', 'show');
        });

        Route::prefix('job_application')->controller(JobApllicationController::class)->group(function () {
            // Job application routes
            Route::get('/', 'index');
            Route::post('update/{id}', 'update');
            Route::post('delete/{id}', 'destroy');
        });
    });

    // Common routes for authenticated users
    Route::get('job', [JobController::class, 'index']);

    Route::prefix('job_application')->controller(JobApllicationController::class)->group(function () {
        Route::post('create', 'store');
        Route::get('my_application', 'getByUser');
        Route::get('show/{id}', 'show');
    });
});

Route::get('latest_jobs', [JobController::class, 'getLatestJob']);
