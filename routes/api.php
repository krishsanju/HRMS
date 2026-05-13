<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\DepartmentController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\LeaveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Employee Management
Route::apiResource('employees', EmployeeController::class);

// Department Management
Route::apiResource('departments', DepartmentController::class);

// Attendance Management
Route::post('attendance/check-in', [AttendanceController::class, 'checkIn']);
Route::post('attendance/check-out', [AttendanceController::class, 'checkOut']);

// Leave Management
Route::post('leave/apply', [LeaveController::class, 'apply']);
Route::patch('leave/approve/{id}', [LeaveController::class, 'approve']);
Route::patch('leave/reject/{id}', [LeaveController::class, 'reject']); // New route for rejection