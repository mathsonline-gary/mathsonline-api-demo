<?php

use Illuminate\Http\Request;
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
    ->middleware(['auth:sanctum'])
    ->group(function () {

        require __DIR__ . '/tutors.php';

        require __DIR__ . '/teachers.php';

        require __DIR__ . '/students.php';

        require __DIR__ . '/admins.php';
    });
