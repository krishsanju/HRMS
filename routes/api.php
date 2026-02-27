<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\DashboardController; // Import new controller

Route::get('/health', fn() => response()->json(['status' => 'HRMS API running']));

// Routes accessible by any authenticated user (e.g., employees)
Route::middleware('auth:sanctum')->group(function () {
    // Employee Attendance
    Route::post('attendance/check-in',[AttendanceController::class,'checkIn']);
    Route::post('attendance/check-out',[AttendanceController::class,'checkOut']);

    // Employee Leave Application
    Route::post('leave/apply',[LeaveController::class,'apply']);
});

// Routes accessible only by authenticated HR/Admin roles
Route::middleware(['auth:sanctum', 'hr.admin'])->group(function () {
    // Employee & Department Management (CRUD)
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('departments', DepartmentController::class);

    // Leave Management (Approval/Rejection)
    Route::patch('leave/{id}/approve',[LeaveController::class,'approve']);
    Route::patch('leave/{id}/reject',[LeaveController::class,'reject']);

    // HR Admin Dashboard Endpoints
    Route::prefix('dashboard')->group(function () {
        Route::get('employees/counts', [DashboardController::class, 'employeeCounts']);
        Route::get('leaves/counts', [DashboardController::class, 'leaveRequestCounts']);
        Route::get('attendance/summary', [DashboardController::class, 'attendanceSummary']);
    });
});