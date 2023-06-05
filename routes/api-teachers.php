<?php

use App\Http\Controllers\Api\Teachers\V1\TeacherController;
use App\Http\Controllers\Web\Auth\AuthenticatedUserController;
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
        Route::get('/me', [AuthenticatedUserController::class, 'show'])
            ->name('me');

        // Teacher module routes.
        Route::get('/teachers', [TeacherController::class, 'index'])
            ->name('teachers.index');

        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])
            ->name('teachers.show');
    });
