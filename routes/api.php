<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\LeaveController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\AuthController;

Route::get('/health', fn() => response()->json(['status' => 'HRMS API running']));

// Public routes for authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes for HR Admin Panel
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);

    // Employee Management
    Route::apiResource('employees', EmployeeController::class);
    Route::get('employees-paginated', [EmployeeController::class, 'index']); // Use index for paginated list

    // Department Management
    Route::apiResource('departments', DepartmentController::class);

    // Attendance Management
    Route::get('attendances', [AttendanceController::class, 'index']);
    Route::post('attendances', [AttendanceController::class, 'store']);
    Route::get('attendances/{id}', [AttendanceController::class, 'show']);
    Route::put('attendances/{id}', [AttendanceController::class, 'update']);
    Route::delete('attendances/{id}', [AttendanceController::class, 'destroy']);
    // Keep check-in/out for employee self-service if needed, but admin uses full CRUD
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

    // Leave Request Management
    Route::get('leaves', [LeaveController::class, 'index']); // Enhanced index for filtering/sorting
    Route::get('leaves/{id}', [LeaveController::class, 'show']);
    Route::post('leave/apply', [LeaveController::class, 'apply']); // Employee self-service
    Route::patch('leave/{id}/approve', [LeaveController::class, 'approve']);
    Route::patch('leave/{id}/reject', [LeaveController::class, 'reject']);
});
