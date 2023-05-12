<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tutor Routes
|--------------------------------------------------------------------------
|
| API routes for tutors.
|
*/

Route::prefix('/tutors')
    ->name('tutors.')
    ->group(function () {
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
    });
