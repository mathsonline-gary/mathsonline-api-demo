<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tutor Routes
|--------------------------------------------------------------------------
|
| API routes for tutors.
|
*/

Route::prefix('/tutors/v1')
    ->name('tutors.v1.')
    ->group(function () {

        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
