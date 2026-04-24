<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\LeaveController;

Route::get('/health', fn() => response()->json(['status' => 'HRMS API running']));

// Routes for Employee and Department management (assuming these are admin-only or have their own auth)
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('departments', DepartmentController::class);

// Attendance routes
Route::post('attendance/check-in',[AttendanceController::class,'checkIn']);
Route::post('attendance/check-out',[AttendanceController::class,'checkOut']);

// Leave management routes
// These routes should ideally be within an authenticated group if not already.
// For this task, we'll apply middleware directly to the new route and assume existing ones handle auth if needed.
Route::post('leave/apply',[LeaveController::class,'apply']);
Route::post('leave/approve/{id}',[LeaveController::class,'approve']);

// AC1: New route to cancel leave by employee
Route::patch('leave/{id}/cancel', [LeaveController::class, 'cancel'])->middleware('auth:api');