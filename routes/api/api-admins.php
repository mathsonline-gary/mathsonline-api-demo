<?php

use App\Http\Controllers\Web\Admins\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admins Routes
|--------------------------------------------------------------------------
|
| API routes for admins.
|
*/

Route::prefix('/admins/v1')
    ->name('admins.v1.')
    ->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
    });
