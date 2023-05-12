<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Developers Routes
|--------------------------------------------------------------------------
|
| API routes for developers.
|
*/

Route::prefix('/developers')
    ->name('developers.')
    ->group(function () {
        Route::get('/me', function (Request $request) {
            return $request->user();
        });
    });
