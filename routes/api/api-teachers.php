<?php

use App\Http\Controllers\Api\Teachers\V1\TeacherController;
use App\Http\Controllers\Web\Teachers\AuthController;
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
        Route::get('/me', [AuthController::class, 'me'])
            ->name('me');

        // Teacher module routes.
        Route::get('/teachers', [TeacherController::class, 'index'])
            ->name('teachers.index');

        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])
            ->name('teachers.show');

        Route::post('/teachers/', [TeacherController::class, 'store'])
            ->name('teachers.store');
    });