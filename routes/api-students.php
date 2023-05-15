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

Route::prefix('/students')
    ->name('students.')
    ->group(function () {
        Route::get('/me', [AuthenticatedUserController::class, 'show']);
    });
