<?php

use App\Http\Controllers\Api\V1\Auth\RegisteredMemberController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Members
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for teacher module.
|
*/

// Teacher module routes.
Route::prefix('/auth')
    ->name('auth.')
    ->group(function () {
        // Register a member.
        Route::post('/register/member', [RegisteredMemberController::class, 'store'])
            ->middleware(['guest'])
            ->name('register.member');
    });
