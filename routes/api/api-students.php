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
        Route::get('/', [StudentController::class, 'index'])
            ->name('index');

        Route::get('/{student}', [StudentController::class, 'show'])
            ->name('show');

        Route::post('/', [StudentController::class, 'store'])
            ->name('store');

        Route::put('/{student}', [StudentController::class, 'update'])
            ->name('update');

        Route::delete('/{student}', [StudentController::class, 'destroy'])
            ->name('destroy');
    });
