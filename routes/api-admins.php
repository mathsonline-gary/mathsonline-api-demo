<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
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
        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
