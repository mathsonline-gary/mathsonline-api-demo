<?php

use App\Http\Controllers\Web\Auth\AuthenticatedUserController;
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
        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
