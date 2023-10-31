<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\Auth\RegisteredMemberController;
use Illuminate\Support\Facades\Route;

// Login route.
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

// Logout route.
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Google OAuth routes.
Route::get('/oauth/google', [GoogleOAuthController::class, 'redirect'])
    ->middleware('guest')
    ->name('oauth.google.redirect');

Route::get('/oauth/google/callback', [GoogleOAuthController::class, 'handle'])
    ->middleware('guest')
    ->name('oauth.google.handle');

// Register route for new members.
Route::post('/members/register', [RegisteredMemberController::class, 'store'])
    ->middleware('guest')
    ->name('members.register');

//Route::post('/register', [RegisteredUserController::class, 'store'])
//    ->middleware('guest')
//    ->name('register');

//Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
//    ->middleware('guest')
//    ->name('password.email');

//Route::post('/reset-password', [NewPasswordController::class, 'store'])
//    ->middleware('guest')
//    ->name('password.store');

//Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
//    ->middleware(['auth', 'signed', 'throttle:6,1'])
//    ->name('verification.verify');

//Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//    ->middleware(['auth', 'throttle:6,1'])
//    ->name('verification.send');
