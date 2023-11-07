<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
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

Route::prefix('v1')
    ->name('api.v1.')
    ->group(function () {

        // Auth module routes
        Route::middleware(['auth:sanctum'])
            ->prefix('/auth')
            ->name('auth.')
            ->group(function () {
                Route::get('/me', [AuthenticatedSessionController::class, 'show'])
                    ->name('me');
            });

        // Auth module routes.
        require __DIR__ . '/api-auth.php';

        // Teacher module routes.
        require __DIR__ . '/api-teachers.php';

        // Student module routes.
        require __DIR__ . '/api-students.php';

        // Classroom module routes.
        require __DIR__ . '/api-classrooms.php';

        // Subscription module routes.
        require __DIR__ . '/api-subscriptions.php';

        // Stripe API routes.
        require __DIR__ . '/api-stripe.php';
    });
