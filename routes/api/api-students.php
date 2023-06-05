<?php

use App\Http\Controllers\Web\Students\AuthController;
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
        Route::get('/me', [AuthController::class, 'me']);
    });
