<?php

use App\Http\Controllers\Web\Members\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routers for members.
Route::prefix('/members')
    ->name('members.')
    ->group(function () {
        Route::post('/register', [AuthController::class, 'register'])
            ->middleware('guest')
            ->name('register');

        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('guest')
            ->name('login');

        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
            ->middleware('guest')
            ->name('password.email');

        Route::post('/reset-password', [AuthController::class, 'resetPassword'])
            ->middleware('guest')
            ->name('password.store');

//        Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
//            ->middleware(['auth:member', 'signed', 'throttle:6,1'])
//            ->name('verification.verify');
//
//        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//            ->middleware(['auth:member', 'throttle:6,1'])
//            ->name('verification.send');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth')
            ->name('logout');
    });
