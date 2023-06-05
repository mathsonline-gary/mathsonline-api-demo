<?php

use App\Http\Controllers\Web\Admins\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routers for teacher.
Route::prefix('/admins')
    ->name('admins.')
    ->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('guest')
            ->name('login');

        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
            ->middleware('guest')
            ->name('password.email');

        Route::post('/reset-password', [AuthController::class, 'resetPassword'])
            ->middleware('guest')
            ->name('password.store');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth')
            ->name('logout');
    });
