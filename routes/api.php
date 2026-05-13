<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\LeaveController;

Route::get('/health', fn() => response()->json(['status' => 'HRMS API running']));

// Routes for HR Admin Panel - Employee and Department Management
Route::apiResource('employees', EmployeeController::class);
Route::apiResource('departments', DepartmentController::class);

// Routes for Employee Attendance
Route::post('attendance/check-in',[AttendanceController::class,'checkIn']);
Route::post('attendance/check-out',[AttendanceController::class,'checkOut']);

// Routes for Leave Management
Route::post('leave/apply',[LeaveController::class,'apply']);
Route::post('leave/approve/{id}',[LeaveController::class,'approve']);
// New route for listing leave requests, accessible by HR Admin
Route::get('leaves', [LeaveController::class, 'index']); // HR Admin level authentication middleware should be applied here.