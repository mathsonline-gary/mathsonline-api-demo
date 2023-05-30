<?php

use App\Http\Controllers\Auth\AuthenticatedUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Students Routes
|--------------------------------------------------------------------------
|
| API routes for students.
|
*/

Route::prefix('/students/v1')
    ->name('students.v1.')
    ->group(function () {
        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
