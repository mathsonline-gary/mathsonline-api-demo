<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admins Routes
|--------------------------------------------------------------------------
|
| API routes for admins.
|
*/

Route::prefix('/admins')
    ->name('admins.')
    ->group(function () {
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
    });
