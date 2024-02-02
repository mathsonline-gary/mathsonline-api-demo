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

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string
     */
    public $queue = 'stripe-webhooks';

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Event $event,
        protected int   $marketId,
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //
    }

    protected function logError(Event $event, string $message): void
    {
        Log::channel('stripe')
            ->error("[$event->type] $message", $event->toArray());
    }
}
