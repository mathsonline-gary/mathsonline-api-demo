<?php

use App\Models\Student;
use App\Models\Teacher;
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
    ->group(function () {
        Route::middleware(['auth'])->get('/me', function (Request $request) {
            $user = $request->user();

            $type = match (get_class($user)) {
                Teacher::class => 'teacher',
                Student::class => 'student',
                default => 'unknown',
            };
            return [
                'type' => $type,
                'data' => $user,
            ];
        });
    });
