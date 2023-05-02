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
        Route::post('/register', [RegisteredUserController::class, 'store']);
        Route::middleware(['default.guard'])->post('/login', [AuthenticatedTokenController::class, 'store']);
        Route::middleware(['auth:sanctum'])->post('/logout', [AuthenticatedTokenController::class, 'destroy']);
        Route::middleware(['auth:sanctum'])->get('/user', [AuthenticatedUserController::class, 'show']);

    });
