<?php

namespace App\Jobs;

use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use App\Services\MembershipService;
use App\Services\SchoolService;
use App\Services\SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\Event;
use Stripe\Subscription as StripeSubscription;

class ProcessStripeSubscriptionWebhookEvent extends ProcessStripeWebhookEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected StripeSubscription $stripeSubscription;
    protected ?School $school;
    protected ?Membership $membership;
    protected ?Subscription $subscription;
    protected SubscriptionService $subscriptionService;
    protected SchoolService $schoolService;
    protected MembershipService $membershipService;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, int $marketId)
    {
        parent::__construct($event, $marketId);

        $this->subscriptionService = new SubscriptionService();
        $this->schoolService = new SchoolService();
        $this->membershipService = new MembershipService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Set properties.
        {
            $object = $this->event->data['object'];

            // Fail the job if the event data is invalid.
            if (!$object instanceof StripeSubscription) {
                $this->logError($this->event, 'Invalid event data.');
                $this->fail('Invalid event data.');
            }

            $this->stripeSubscription = $object;

            // Fail the job if the associated school not found.
            if (is_null($this->school = $this->schoolService->findByStripeId($this->stripeSubscription->customer))) {
                $this->logError($this->event, 'The associated school not found.');
                $this->fail('The associated school not found.');
            }

            // Log error if there is an associated membership.
            if (is_null($this->membership = $this->membershipService->findByStripeId($this->stripeSubscription->items->first()->price->id))) {
                $this->logError($this->event, 'The associated membership not found.');
            }

            $this->subscription = $this->school->subscriptions()->firstWhere('stripe_id', $this->stripeSubscription->id);
        }

        switch ($this->event->type) {
            case Event::TYPE_CUSTOMER_SUBSCRIPTION_CREATED:
                $this->handleCustomerSubscriptionCreated();
                break;

            case Event::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED:
                $this->handleCustomerSubscriptionUpdated();
                break;

            case Event::TYPE_CUSTOMER_SUBSCRIPTION_DELETED:
                $this->handleCustomerSubscriptionDeleted();
                break;

            default:
                $this->logError($this->event, 'Event unhandled.');
        }
    }

    /**
     * Handle the customer subscription created event.
     *
     * @return void
     */
    protected function handleCustomerSubscriptionCreated(): void
    {
        // Check if the Stripe subscription already exists.
        if (!is_null($this->subscription)) {
            $this->logError($this->event, 'The associated subscription already exists.');
            return;
        }

        // Check if the Stripe subscription has an associated membership with an active campaign.
        if (!$this->membership->campaign->isActive()) {
            $this->logError($this->event, 'The associated membership campaign is not active.');
            $this->fail('The associated membership campaign is not active.');
        }

        // Otherwise, create a new subscription.
        $this->subscriptionService->create($attributes);
    }

    /**
     * Handle the customer subscription updated event.
     *
     * @return void
     */
    protected function handleCustomerSubscriptionUpdated(): void
    {
    }

    /**
     * Handle the customer subscription deleted event.
     *
     * @return void
     */
    protected function handleCustomerSubscriptionDeleted(): void
    {
    }

}
