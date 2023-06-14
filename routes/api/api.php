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

Route::middleware(['auth:sanctum'])
    ->group(function () {

        require __DIR__ . '/api-tutors.php';

        require __DIR__ . '/api-teachers.php';

        require __DIR__ . '/api-students.php';

        require __DIR__ . '/api-admins.php';

        require __DIR__ . '/api-developers.php';
    });
