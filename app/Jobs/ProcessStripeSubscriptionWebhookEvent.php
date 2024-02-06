<?php

namespace App\Jobs;

use App\Models\Membership;
use App\Models\School;
use App\Models\Subscription;
use App\Services\MembershipService;
use App\Services\SchoolService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
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

    // Constants for error messages.
    const EVENT_INVALID = 'Invalid event data.';
    const ERROR_SCHOOL_NOT_FOUND = 'The associated school not found.';
    const ERROR_MEMBERSHIP_NOT_FOUND = 'The associated membership not found.';
    const ERROR_SUBSCRIPTION_EXISTS = 'The associated subscription already exists.';
    const ERROR_EVENT_BACKDATED = 'Backdated event.';
    const ERROR_EVENT_UNHANDLED = 'Unhandled event.';
    const ERROR_SUBSCRIPTION_NOT_CREATED = 'The associated subscription has not been created yet.';
    const ERROR_SUBSCRIPTION_CANCELED = 'The subscription has already been canceled.';
    const ERROR_MEMBERSHIP_INACTIVE = 'The associated membership campaign is not active.';

    /**
     * The Stripe subscription object from the event data.
     *
     * @var StripeSubscription
     */
    protected StripeSubscription $stripeSubscription;


    /**
     * The associated school.
     *
     * @var School|null
     */
    protected ?School $school;

    /**
     * The associated membership.
     *
     * @var Membership|null
     */
    protected ?Membership $membership;

    /**
     * The associated subscription.
     *
     * @var Subscription|null
     */
    protected ?Subscription $subscription;

    protected string $stripeId;
    protected Carbon $startsAt;
    protected ?Carbon $cancelsAt;
    protected Carbon $currentPeriodStartsAt;
    protected Carbon $currentPeriodEndsAt;
    protected ?Carbon $canceledAt;
    protected ?Carbon $endedAt;
    protected string $status;

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
                $this->logError($this->event, self::EVENT_INVALID);
                $this->fail(self::EVENT_INVALID);

                return;
            }

            $this->stripeSubscription = $object;

            // Fail the job if the associated school not found.
            if (is_null($this->school = $this->schoolService->findByStripeId($this->stripeSubscription->customer))) {
                $this->logError($this->event, self::ERROR_SCHOOL_NOT_FOUND);
                $this->fail(self::ERROR_SCHOOL_NOT_FOUND);

                return;
            }

            $this->subscription = $this->school->subscriptions()->firstWhere('stripe_id', $this->stripeSubscription->id);

            // Handle the out-of-order events delivery. Skip if the event is backdated.
            if ($this->subscription?->last_stripe_event_at?->timestamp > $this->event->created) {
                $this->logError($this->event, self::ERROR_EVENT_BACKDATED);
                $this->delete();

                return;
            }

            $this->membership = $this->membershipService->findByStripeId($this->stripeSubscription->items->first()->price->id);
            $this->stripeId = $this->stripeSubscription->id;
            $this->startsAt = new Carbon($this->stripeSubscription->start_date);
            $this->cancelsAt = $this->stripeSubscription->cancel_at ? new Carbon($this->stripeSubscription->cancel_at) : null;
            $this->currentPeriodStartsAt = new Carbon($this->stripeSubscription->current_period_start);
            $this->currentPeriodEndsAt = new Carbon($this->stripeSubscription->current_period_end);
            $this->canceledAt = $this->stripeSubscription->canceled_at ? new Carbon($this->stripeSubscription->canceled_at) : null;
            $this->endedAt = $this->stripeSubscription->ended_at ? new Carbon($this->stripeSubscription->ended_at) : null;
            $this->status = $this->stripeSubscription->status;
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
                $this->logError($this->event, self::ERROR_EVENT_UNHANDLED);
                $this->delete();
        }
    }

    /**
     * Handle the customer subscription created event.
     *
     * @return void
     */
    protected function handleCustomerSubscriptionCreated(): void
    {
        // Skip if the Stripe subscription already exists.
        if ($this->subscription) {
            $this->logError($this->event, self::ERROR_SUBSCRIPTION_EXISTS);
            $this->delete();

            return;
        }

        // Fail the job if the associated membership not found.
        if (is_null($this->membership)) {
            $this->logError($this->event, self::ERROR_MEMBERSHIP_NOT_FOUND);
            $this->fail(self::ERROR_MEMBERSHIP_NOT_FOUND);

            return;
        }

        // Check if the Stripe subscription has an associated membership with an active campaign.
        if (!$this->membership->campaign->isActive()) {
            $this->logError($this->event, self::ERROR_MEMBERSHIP_INACTIVE);
            // We don't terminate the job here because we may want to our staff to be able to manually create a subscription to an expired membership for the customer.
        }

        // Otherwise, create a new subscription.
        $this->subscriptionService->create([
            'membership_id' => $this->membership->id,
            'school_id' => $this->school->id,
            'stripe_id' => $this->stripeId,
            'starts_at' => $this->startsAt,
            'cancels_at' => $this->cancelsAt,
            'current_period_starts_at' => $this->currentPeriodStartsAt,
            'current_period_ends_at' => $this->currentPeriodEndsAt,
            'canceled_at' => $this->canceledAt,
            'ended_at' => $this->endedAt,
            'status' => $this->status,
            'last_stripe_event_at' => $this->event->created,
        ]);

        $this->logSuccess($this->event, 'The subscription was created successfully.');
    }

    /**
     * Handle the customer subscription updated event.
     *
     * @return void
     */
    protected function handleCustomerSubscriptionUpdated(): void
    {
        // Fail the job if the associated membership not found.
        if (is_null($this->membership)) {
            $this->logError($this->event, self::ERROR_MEMBERSHIP_NOT_FOUND);
            $this->fail(self::ERROR_MEMBERSHIP_NOT_FOUND);

            return;
        }

        // Release the job if the subscription has not been created yet.
        if (is_null($this->subscription)) {
            $this->logError($this->event, self::ERROR_SUBSCRIPTION_NOT_CREATED);
            $this->release(10);

            return;
        }

        // Skip if the subscription has been cancelled.
        if ($this->subscription->isCanceled()) {
            $this->logError($this->event, self::ERROR_SUBSCRIPTION_CANCELED);
            $this->delete();

            return;
        }

        // Otherwise, update the subscription.
        $this->subscriptionService->update($this->subscription, [
            'membership_id' => $this->membership->id,
            'starts_at' => $this->startsAt,
            'cancels_at' => $this->cancelsAt,
            'current_period_starts_at' => $this->currentPeriodStartsAt,
            'current_period_ends_at' => $this->currentPeriodEndsAt,
            'canceled_at' => $this->canceledAt,
            'ended_at' => $this->endedAt,
            'status' => $this->status,
            'last_stripe_event_at' => $this->event->created,
        ]);

        $this->logSuccess($this->event, 'The subscription was updated successfully.');
    }

    /**
     * Handle the customer subscription deleted event.
     *
     * @return void
     */
    protected function handleCustomerSubscriptionDeleted(): void
    {
        // Release the job if the subscription has not been created yet.
        if (is_null($this->subscription)) {
            $this->logError($this->event, self::ERROR_SUBSCRIPTION_NOT_CREATED);
            $this->release(10);

            return;
        }

        // Skip if the subscription has been canceled.
        if ($this->subscription->isCanceled()) {
            $this->logError($this->event, self::ERROR_SUBSCRIPTION_CANCELED);
            $this->delete();

            return;
        }

        // Otherwise, update the subscription.
        $this->subscriptionService->update($this->subscription, [
            'cancels_at' => $this->cancelsAt,
            'current_period_starts_at' => $this->currentPeriodStartsAt,
            'current_period_ends_at' => $this->currentPeriodEndsAt,
            'canceled_at' => $this->canceledAt,
            'ended_at' => $this->endedAt,
            'status' => $this->status,
            'last_stripe_event_at' => $this->event->created,
        ]);

        $this->logSuccess($this->event, 'The subscription was canceled successfully.');
    }

}
