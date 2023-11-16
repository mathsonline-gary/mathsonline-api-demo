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
        TestResponse::macro('assertEmailVerificationRequired', function (): void {
            $this->assertStatus(409)
                ->assertJson(['message' => 'Your email address is not verified.']);
        });

        TestResponse::macro('assertJsonSuccess', function (): void {
            $this->assertJsonFragment(['success' => true]);
        });
    }
}
