<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\AuthController; // Import AuthController

Route::get('/health', fn() => response()->json(['status' => 'HRMS API running']));

// Public authentication routes
Route::post('/login', [AuthController::class, 'login']);

// Protected HRMS API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // HR Admin specific routes
    Route::middleware('role:hr_admin')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::post('leave/approve/{id}', [LeaveController::class, 'approve']);
    });

    // Employee specific routes
    Route::middleware('role:employee')->group(function () {
        Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);
        Route::post('leave/apply', [LeaveController::class, 'apply']);
    });
});