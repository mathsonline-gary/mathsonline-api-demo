<?php

use App\Http\Controllers\Web\Tutors\AuthController;
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

        Route::get('/me', [AuthController::class, 'me']);
    });
