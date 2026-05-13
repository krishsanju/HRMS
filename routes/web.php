<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// New route for HR Admin Panel Leave Management UI
// HR Admin level authentication middleware should be applied here.
Route::get('/admin/leave-management', function () {
    return view('admin.leave-management');
});