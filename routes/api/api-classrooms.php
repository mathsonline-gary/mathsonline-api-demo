<?php

use App\Http\Controllers\Api\V1\ClassroomController;
use App\Http\Controllers\Api\V1\ClassroomGroupController;
use App\Http\Controllers\Api\V1\ClassroomSecondaryTeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Classrooms
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for classroom and group module.
|
*/

Route::prefix('classrooms')
    ->name('classrooms.')
    ->group(function () {
        Route::get('/', [ClassroomController::class, 'index'])
            ->name('index');

        Route::get('/{classroom}', [ClassroomController::class, 'show'])
            ->name('show');

        Route::post('/', [ClassroomController::class, 'store'])
            ->name('store');

        Route::put('/{classroom}', [ClassroomController::class, 'update'])
            ->name('update');

        Route::delete('/{classroom}', [ClassroomController::class, 'destroy'])
            ->name('destroy');

        // Classroom group routes.
        Route::post('/{classroom}/groups', [ClassroomGroupController::class, 'store'])
            ->name('groups.store');

        Route::put('/{classroom}/groups/{classroomGroup}', [ClassroomGroupController::class, 'update'])
            ->name('groups.update');

        Route::delete('/{classroom}/groups/{classroomGroup}', [ClassroomGroupController::class, 'destroy'])
            ->name('groups.destroy');

        // Classroom secondary teacher routes.
        Route::post('/{classroom}/secondary-teachers/{teacher}', [ClassroomSecondaryTeacherController::class, 'store'])
            ->name('secondary-teachers.store');

        Route::delete('/{classroom}/secondary-teachers/{teacher}', [ClassroomSecondaryTeacherController::class, 'destroy'])
            ->name('secondary-teachers.destroy');
    });
