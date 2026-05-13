<?php

use Illuminate\Support\Facades\Route;

Route::view('/{any?}', 'admin')->where('any', '.*');
