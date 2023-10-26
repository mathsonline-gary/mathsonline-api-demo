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

Route::prefix('students')
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

    });
