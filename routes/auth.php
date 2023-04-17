<?php

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DeveloperAuthController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\Auth\TeacherAuthController;
use App\Http\Controllers\Auth\TutorAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// Tutor authentication routes.
Route::prefix('tutors')
    ->group(function () {
        Route::post('/login', [TutorAuthController::class, 'login'])
            ->name('tutors.login');

        Route::post('/logout', [TutorAuthController::class, 'logout'])
            ->name('tutors.logout');
    });

// Teacher authentication routes.
Route::prefix('teachers')
    ->group(function () {
        Route::post('/login', [TeacherAuthController::class, 'login'])
            ->name('teachers.login');

        Route::post('/logout', [TeacherAuthController::class, 'logout'])
            ->name('teachers.logout');
    });

// Student authentication routes.
Route::prefix('students')
    ->group(function () {
        Route::post('/login', [StudentAuthController::class, 'login'])
            ->name('students.login');

        Route::post('/logout', [StudentAuthController::class, 'logout'])
            ->name('students.logout');
    });

// Admin authentication routes.
Route::prefix('admins')
    ->group(function () {
        Route::post('/login', [AdminAuthController::class, 'login'])
            ->name('admins.login');

        Route::post('/logout', [AdminAuthController::class, 'logout'])
            ->name('admins.logout');
    });

// Developer authentication routes.
Route::prefix('developers')
    ->group(function () {
        Route::post('/login', [DeveloperAuthController::class, 'login'])
            ->name('developers.login');

        Route::post('/logout', [DeveloperAuthController::class, 'logout'])
            ->name('developers.logout');
    });

/*Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest')
                ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest')
                ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['auth', 'signed', 'throttle:6,1'])
                ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware(['auth', 'throttle:6,1'])
                ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                ->middleware('auth')
                ->name('logout');*/
