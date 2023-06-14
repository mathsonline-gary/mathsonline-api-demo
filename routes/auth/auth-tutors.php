<?php

use App\Http\Controllers\Web\Tutors\AuthController;
use Illuminate\Support\Facades\Route;

// Auth routers for tutor.
Route::prefix('/tutors')
    ->name('tutors.')
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
//            ->middleware(['auth:tutor', 'signed', 'throttle:6,1'])
//            ->name('verification.verify');
//
//        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//            ->middleware(['auth:tutor', 'throttle:6,1'])
//            ->name('verification.send');

        Route::post('/logout', [AuthController::class, 'logout'])
            ->middleware('auth')
            ->name('logout');
    });
