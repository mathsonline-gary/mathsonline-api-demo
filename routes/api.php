<?php

use App\Http\Controllers\Api\V1\Auth\AuthenticatedTokenController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/v1')
    ->name('api.v1.')
    ->group(function () {

        // Auth routes.
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->name('register');

        Route::post('/login', [AuthenticatedTokenController::class, 'store'])
            ->middleware(['default.guard'])
            ->name('login');

        Route::post('/logout', [AuthenticatedTokenController::class, 'destroy'])
            ->middleware(['auth:sanctum'])
            ->name('logout');

        Route::get('/user', [AuthenticatedUserController::class, 'show'])
            ->middleware(['auth:sanctum'])
            ->name('user.authenticated');

    });
