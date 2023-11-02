<?php

use App\Http\Controllers\Api\V1\ClassroomController;
use App\Http\Controllers\Api\V1\ClassroomGroupController;
use App\Http\Controllers\Api\V1\ClassroomStudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Classrooms
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for classroom and group module.
|
*/

Route::middleware(['auth:sanctum'])
    ->prefix('classrooms')
    ->name('classrooms.')
    ->group(function () {

        // List classrooms.
        Route::get('/', [ClassroomController::class, 'index'])
            ->name('index');

        // Get a classroom.
        Route::get('/{classroom}', [ClassroomController::class, 'show'])
            ->name('show');

        // Create a classroom.
        Route::post('/', [ClassroomController::class, 'store'])
            ->name('store');

        // Update a classroom.
        Route::put('/{classroom}', [ClassroomController::class, 'update'])
            ->name('update');

        // Delete a classroom.
        Route::delete('/{classroom}', [ClassroomController::class, 'destroy'])
            ->name('destroy');

        // List students in a classroom.
        Route::get('/{classroom}/students', [ClassroomStudentController::class, 'index'])
            ->name('students.index');

        // Classroom group routes.
        Route::put('/{classroom}/groups', [ClassroomGroupController::class, 'update'])
            ->name('groups.update');

    });
