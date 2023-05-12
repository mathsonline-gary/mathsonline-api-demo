<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Students Routes
|--------------------------------------------------------------------------
|
| API routes for students.
|
*/

Route::prefix('/students')
    ->name('students.')
    ->group(function () {
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
    });
