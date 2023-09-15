<?php

use App\Http\Controllers\Web\Members\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Member Routes
|--------------------------------------------------------------------------
|
| API routes for members.
|
*/

Route::prefix('/members/v1')
    ->name('api.members.v1.')
    ->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
    });
