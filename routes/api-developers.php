<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
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
        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
