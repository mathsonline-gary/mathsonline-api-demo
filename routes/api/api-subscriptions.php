<?php

use App\Http\Controllers\Api\V1\SubscriptionController;

Route::prefix('subscriptions')
    ->name('subscriptions.')
    ->group(function () {
        // Create a subscription.
        Route::post('/', [SubscriptionController::class, 'store'])
            ->name('store');

    });
