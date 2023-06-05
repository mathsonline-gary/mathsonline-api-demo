<?php

use App\Http\Controllers\Web\Developers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Developers Routes
|--------------------------------------------------------------------------
|
| API routes for developers.
|
*/

Route::prefix('/developers/v1')
    ->name('developers.v1.')
    ->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
    });
