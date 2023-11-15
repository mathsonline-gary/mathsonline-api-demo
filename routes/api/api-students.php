<?php

use App\Http\Controllers\Api\V1\StudentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Students
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for student module.
|
*/

Route::middleware(['auth:sanctum'])
    ->prefix('students')
    ->name('students.')
    ->group(function () {
        // List students.
        Route::get('/', [StudentController::class, 'index'])
            ->name('index');

        // Get a student.
        Route::get('/{student}', [StudentController::class, 'show'])
            ->name('show');

        // Create a student.
        Route::post('/', [StudentController::class, 'store'])
            ->name('store');

        // Update a student.
        Route::put('/{student}', [StudentController::class, 'update'])
            ->name('update');

        // Delete a student.
        Route::delete('/{student}', [StudentController::class, 'destroy'])
            ->name('destroy');

        // Add a student to a classroom group.
        Route::put('/{student}/move-to-classroom-group/{classroomGroup}', [StudentController::class, 'addGroup'])
            ->name('classroom-groups.add');

        // Set student's classroom groups (removing all previous groups).
        Route::put('/{student}/classroom-groups', [StudentController::class, 'setGroups'])
            ->name('classroom-groups.set');

        // Remove a student from a classroom.
        Route::delete('/{student}/classrooms/{classroom}', [StudentController::class, 'removeFromClassroom'])
            ->name('classrooms.remove');
    });
