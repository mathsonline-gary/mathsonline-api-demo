<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

/**
 * The base class for processing Stripe webhook events.
 */
class ProcessStripeWebhookEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const QUEUE_NAME = 'stripe-webhook';

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Event $event,
        protected int   $marketId,
    )
    {
        $this->queue = self::QUEUE_NAME;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }

    /**
     * Log an error message for the given event.
     *
     * @param Event  $event
     * @param string $message
     *
     * @return void
     */
    protected function logError(Event $event, string $message): void
    {
        Log::channel('stripe')
            ->error("[$event->type] $message", $event->toArray());
    }

    protected function logSuccess(Event $event, string $message): void
    {
        Log::channel('stripe')
            ->info("[$event->type] $message", $event->toArray());
    }
}
