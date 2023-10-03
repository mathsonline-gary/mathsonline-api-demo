<?php

use App\Http\Controllers\Api\V1\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Teachers
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for teacher module.
|
*/

// Teacher module routes.
Route::prefix('/teachers')
    ->name('teachers.')
    ->group(function () {
        Route::get('/', [TeacherController::class, 'index'])
            ->name('index');

        Route::get('/{teacher}', [TeacherController::class, 'show'])
            ->name('show');

        Route::post('/', [TeacherController::class, 'store'])
            ->name('store');

        Route::put('/{teacher}', [TeacherController::class, 'update'])
            ->name('update');

        Route::delete('/{teacher}', [TeacherController::class, 'destroy'])
            ->name('destroy');
    });
