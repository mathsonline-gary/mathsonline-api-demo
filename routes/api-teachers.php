<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Teacher Routes
|--------------------------------------------------------------------------
|
| API routes for teachers.
|
*/

Route::prefix('/teachers/v1')
    ->name('teachers.v1.')
    ->group(function () {
        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
