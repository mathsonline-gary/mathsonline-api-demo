<?php

use App\Http\Controllers\Api\V1\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::name('stripe.')
    ->prefix('stripe')
    ->group(function () {
        Route::post('/{marketId}/webhook', [StripeWebhookController::class, 'handle'])
            ->name('webhook.handle');
    });
