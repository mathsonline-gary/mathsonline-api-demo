<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
|
| API routes for teachers.
|
*/

Route::prefix('/teachers')
    ->name('teachers.')
    ->group(function () {
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
    });
