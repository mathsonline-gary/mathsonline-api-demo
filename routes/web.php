<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__ . '/auth/auth-teachers.php';

require __DIR__ . '/auth/auth-students.php';

require __DIR__ . '/auth/auth-admins.php';

require __DIR__ . '/auth/auth-developers.php';

require __DIR__ . '/auth/auth-tutors.php';
