<?php

use App\Http\Controllers\Api\V1\SubscriptionController;

Route::middleware(['auth:sanctum', 'verified'])
    ->prefix('subscriptions')
    ->name('subscriptions.')
    ->group(function () {
        // Create a subscription.
        Route::post('/', [SubscriptionController::class, 'store'])
            ->name('store');

    });
