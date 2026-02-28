<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\PasswordResetController; // Import new controller

Route::get('/health', fn() => response()->json(['status' => 'HRMS API running']));

// Public routes for password reset
Route::post('password/forgot', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('departments', DepartmentController::class);

    Route::post('attendance/check-in',[AttendanceController::class,'checkIn']);
    Route::post('attendance/check-out',[AttendanceController::class,'checkOut']);

    Route::post('leave/apply',[LeaveController::class,'apply']);
    Route::post('leave/approve/{id}',[LeaveController::class,'approve']);

    // Authenticated password update
    Route::put('user/password', [EmployeeController::class, 'updatePassword']);
});