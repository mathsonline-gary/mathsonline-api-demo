<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Testing\TestResponse;

/**
 * This service provider extends the TestResponse class with custom macros.
 *
 */
class TestResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        TestResponse::macro('assertEmailVerificationRequired', function () {
            return $this->assertStatus(409)
                ->assertJson(['message' => 'Your email address is not verified.']);
        });

        TestResponse::macro('assertJsonSuccessful', function () {
            return $this->assertJsonFragment(['success' => true]);
        });

        TestResponse::macro('assertUnsubscribed', function () {
            return $this->assertStatus(409)
                ->assertJsonFragment(['message' => 'You do not have an active subscription.']);
        });

        TestResponse::macro('assertStripeWebhookSuccessful', function (string $message = 'Webhook handled.') {
            return $this->assertOk()
                ->assertJsonSuccessful()
                ->assertJsonFragment(['message' => $message]);
        });

        TestResponse::macro('assertStripeWebhookMissing', function (string $message = 'Webhook unhandled.') {
            return $this->assertOk()
                ->assertJsonSuccessful()
                ->assertJsonFragment(['message' => $message]);
        });
    }
}
