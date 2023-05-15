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

Route::prefix('/tutors')
    ->name('tutors.')
    ->group(function () {

        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
