<?php

use App\Http\Controllers\Api\Teachers\V1\ClassroomController;
use App\Http\Controllers\Api\Teachers\V1\ClassroomGroupController;
use App\Http\Controllers\Api\Teachers\V1\ClassroomSecondaryTeacherController;
use App\Http\Controllers\Api\Teachers\V1\StudentController;
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
    ->name('api.teachers.v1.')
    ->group(function () {
        Route::get('/me', [AuthController::class, 'me'])
            ->name('me');

        // Teacher module routes.
        Route::get('/teachers', [TeacherController::class, 'index'])
            ->name('teachers.index');

        Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])
            ->name('teachers.show');

        Route::post('/teachers', [TeacherController::class, 'store'])
            ->name('teachers.store');

        Route::put('/teachers/{teacher}', [TeacherController::class, 'update'])
            ->name('teachers.update');

        Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy'])
            ->name('teachers.destroy');

        // Classroom module routes.
        Route::get('/classrooms', [ClassroomController::class, 'index'])
            ->name('classrooms.index');

        Route::get('/classrooms/{classroom}', [ClassroomController::class, 'show'])
            ->name('classrooms.show');

        Route::post('/classrooms', [ClassroomController::class, 'store'])
            ->name('classrooms.store');

        Route::put('/classrooms/{classroom}', [ClassroomController::class, 'update'])
            ->name('classrooms.update');

        Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy'])
            ->name('classrooms.destroy');

        // Classroom group routes.
        Route::post('/classrooms/{classroom}/groups', [ClassroomGroupController::class, 'store'])
            ->name('classrooms.groups.store');

        Route::put('/classrooms/{classroom}/groups/{classroomGroup}', [ClassroomGroupController::class, 'update'])
            ->name('classrooms.groups.update');

        Route::delete('/classrooms/{classroom}/groups/{classroomGroup}', [ClassroomGroupController::class, 'destroy'])
            ->name('classrooms.groups.destroy');

        // Classroom secondary teacher routes.
        Route::post('/classrooms/{classroom}/secondary-teachers/{teacher}', [ClassroomSecondaryTeacherController::class, 'store'])
            ->name('classrooms.secondary-teachers.store');

        Route::delete('/classrooms/{classroom}/secondary-teachers/{teacher}', [ClassroomSecondaryTeacherController::class, 'destroy'])
            ->name('classrooms.secondary-teachers.destroy');

        // Student routes.
        Route::get('/students', [StudentController::class, 'index'])
            ->name('students.index');

        Route::get('/students/{student}', [StudentController::class, 'show'])
            ->name('students.show');

        Route::put('/students/{student}', [StudentController::class, 'update'])
            ->name('students.update');

        Route::delete('/students/{student}', [StudentController::class, 'destroy'])
            ->name('students.destroy');
    });
