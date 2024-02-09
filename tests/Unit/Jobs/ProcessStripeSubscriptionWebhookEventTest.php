<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessStripeSubscriptionWebhookEvent;
use App\Models\Membership;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Queue;
use Stripe\Event as StripeEvent;
use Stripe\StripeClient;
use Stripe\Subscription as StripeSubscription;
use Tests\TestCase;

class ProcessStripeSubscriptionWebhookEventTest extends TestCase
{
    use RefreshDatabase;

    protected StripeClient $stripe;

    protected int $marketId = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripe = $this->newStripeClient($this->marketId);
    }

    public function test_it_fails_the_job_if_the_data_object_is_not_instance_of_stripe_subscription()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_CREATED,
            'limit' => 1,
        ])->first();

        $event->data['object'] = null;

        Queue::after(function (JobProcessed $event) {
            $this->assertTrue($event->job->hasFailed());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_fails_the_job_if_no_associated_school_found()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED,
            'limit' => 1,
        ])->first();

        Queue::after(function (JobProcessed $event) {
            $this->assertTrue($event->job->hasFailed());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_deletes_the_job_if_the_event_is_backdated()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $school = $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Create a fake subscription where the last stripe event is greater than the event created time.
        $this->fakeSubscription($school, Subscription::STATUS_ACTIVE, null, 1, [
            'stripe_id' => $event->data['object']['id'],
            'last_stripe_event_at' => $event->created + 1,
        ]);

        Queue::after(function (JobProcessed $event) {
            // Assert that the job has been manually deleted.
            $this->assertFalse($event->job->hasFailed());
            $this->assertTrue($event->job->isDeleted());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_skips_to_create_subscription_if_the_associated_subscription_exists()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_CREATED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $school = $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Create a fake subscription.
        $this->fakeSubscription($school, Subscription::STATUS_ACTIVE, null, 1, [
            'stripe_id' => $event->data['object']['id'],
        ]);

        Queue::after(function (JobProcessed $event) {
            $this->assertFalse($event->job->hasFailed());
            $this->assertTrue($event->job->isDeleted());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_fails_to_create_subscription_if_the_associated_membership_not_found()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_CREATED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Remove the membership from the database.
        Membership::firstWhere('stripe_id', $stripeSubscription->items->first()->price->id)->delete();

        Queue::after(function (JobProcessed $event) {
            $this->assertTrue($event->job->hasFailed());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_logs_error_during_creating_subscription_if_the_associated_membership_campaign_is_inactive()
    {
        $stripeEvent = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_CREATED,
            'limit' => 1,
        ])->first();


        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $stripeEvent->data['object'];

        // Create a fake school.
        $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Deactivate the membership campaign.
        Membership::firstWhere('stripe_id', $stripeSubscription->items->first()->price->id)
            ->campaign()
            ->update(['expires_at' => new Carbon($stripeEvent->created - 1)]);

        Queue::after(function (JobProcessed $event) {
            // Assert that the job has been processed successfully.
            $this->assertFalse($event->job->hasFailed());
            $this->assertEquals(1, $event->job->attempts());

            // Assert that the subscription has been created.
            $this->assertDatabaseCount('subscriptions', 1);
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($stripeEvent, $this->marketId);
    }

    public function test_it_creates_the_subscription()
    {
        $stripeEvent = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_CREATED,
            'limit' => 1,
        ])->first();


        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $stripeEvent->data['object'];

        // Create a fake school.
        $school = $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        $membership = Membership::firstWhere('stripe_id', $stripeSubscription->items->first()->price->id);

        Queue::after(function (JobProcessed $event) use ($stripeEvent, $stripeSubscription, $school, $membership) {
            // Assert that the job has been processed successfully.
            $this->assertFalse($event->job->hasFailed());

            // Assert that the subscription has been created correctly.
            $this->assertDatabaseCount('subscriptions', 1);
            $subscription = Subscription::first();
            $this->assertSubscriptionAttributes([
                'school_id' => $school->id,
                'membership_id' => $membership->id,
                'stripe_id' => $stripeSubscription->id,
                'starts_at' => $stripeSubscription->start_date,
                'cancels_at' => $stripeSubscription->cancel_at,
                'current_period_starts_at' => $stripeSubscription->current_period_start,
                'current_period_ends_at' => $stripeSubscription->current_period_end,
                'canceled_at' => $stripeSubscription->canceled_at,
                'ended_at' => $stripeSubscription->ended_at,
                'status' => $stripeSubscription->status,
                'last_stripe_event_at' => $stripeEvent->created,
            ], $subscription);
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($stripeEvent, $this->marketId);
    }

    public function test_it_fails_to_update_subscription_if_the_associated_membership_not_found()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Remove the membership from the database.
        Membership::firstWhere('stripe_id', $stripeSubscription->items->first()->price->id)->delete();

        Queue::after(function (JobProcessed $event) {
            // Assert that the job was manually failed.
            $this->assertTrue($event->job->hasFailed());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_releases_update_job_if_the_associated_subscription_not_found()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        Queue::after(function (JobProcessed $event) {
            // Assert that the job was manually released.
            $this->assertFalse($event->job->hasFailed());
            $this->assertTrue($event->job->isReleased());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_skips_to_update_subscription_if_the_associated_subscription_has_been_canceled()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $school = $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Create a fake subscription.
        $this->fakeSubscription($school, Subscription::STATUS_CANCELED, null, 1, [
            'stripe_id' => $event->data['object']['id'],
        ]);

        Queue::after(function (JobProcessed $event) {
            // Assert that the job was manually deleted.
            $this->assertFalse($event->job->hasFailed());
            $this->assertTrue($event->job->isDeleted());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_updates_the_subscription()
    {
        $stripeEvent = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_UPDATED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $stripeEvent->data['object'];

        // Create a fake school.
        $school = $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Create a fake subscription.
        $this->fakeSubscription($school, Subscription::STATUS_ACTIVE, null, 1, [
            'stripe_id' => $stripeEvent->data['object']['id'],
        ]);

        $membership = Membership::firstWhere('stripe_id', $stripeSubscription->items->first()->price->id);

        Queue::after(function (JobProcessed $event) use ($stripeEvent, $stripeSubscription, $school, $membership) {
            // Assert that the job was processed successfully.
            $this->assertFalse($event->job->hasFailed());
            $this->assertEquals(1, $event->job->attempts());

            // Assert that the subscription has been updated correctly.
            $this->assertDatabaseCount('subscriptions', 1);
            $subscription = Subscription::first();
            $this->assertSubscriptionAttributes([
                'school_id' => $school->id,
                'membership_id' => $membership->id,
                'stripe_id' => $stripeSubscription->id,
                'starts_at' => $stripeSubscription->start_date,
                'cancels_at' => $stripeSubscription->cancel_at,
                'current_period_starts_at' => $stripeSubscription->current_period_start,
                'current_period_ends_at' => $stripeSubscription->current_period_end,
                'canceled_at' => $stripeSubscription->canceled_at,
                'ended_at' => $stripeSubscription->ended_at,
                'status' => $stripeSubscription->status,
                'last_stripe_event_at' => $stripeEvent->created,
            ], $subscription);
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($stripeEvent, $this->marketId);
    }

    public function test_it_releases_delete_job_if_the_associated_subscription_not_found()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_DELETED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        Queue::after(function (JobProcessed $event) {
            // Assert that the job was manually released.
            $this->assertFalse($event->job->hasFailed());
            $this->assertTrue($event->job->isReleased());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_skips_the_job_if_the_associated_subscription_has_been_canceled()
    {
        $event = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_DELETED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $event->data['object'];

        // Create a fake school.
        $school = $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Create a fake subscription.
        $this->fakeSubscription($school, Subscription::STATUS_CANCELED, null, 1, [
            'stripe_id' => $event->data['object']['id'],
        ]);

        Queue::after(function (JobProcessed $event) {
            // Assert that the job was manually deleted.
            $this->assertFalse($event->job->hasFailed());
            $this->assertTrue($event->job->isDeleted());
            $this->assertEquals(1, $event->job->attempts());
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($event, $this->marketId);
    }

    public function test_it_cancels_the_subscription()
    {
        $stripeEvent = $this->stripe->events->all([
            'type' => StripeEvent::TYPE_CUSTOMER_SUBSCRIPTION_DELETED,
            'limit' => 1,
        ])->first();

        /** @var StripeSubscription $stripeSubscription */
        $stripeSubscription = $stripeEvent->data['object'];

        // Create a fake school.
        $school = $this->fakeSchool(1, ['stripe_id' => $stripeSubscription->customer]);

        // Create a fake subscription.
        $this->fakeSubscription($school, Subscription::STATUS_ACTIVE, null, 1, [
            'stripe_id' => $stripeEvent->data['object']['id'],
        ]);

        Queue::after(function (JobProcessed $event) use ($stripeEvent, $stripeSubscription, $school) {
            // Assert that the job was processed successfully.
            $this->assertFalse($event->job->hasFailed());
            $this->assertEquals(1, $event->job->attempts());

            // Assert that the subscription has been updated correctly.
            $this->assertDatabaseCount('subscriptions', 1);
            $subscription = Subscription::first();
            $this->assertSubscriptionAttributes([
                'school_id' => $school->id,
                'stripe_id' => $stripeSubscription->id,
                'cancels_at' => $stripeSubscription->cancel_at,
                'current_period_starts_at' => $stripeSubscription->current_period_start,
                'current_period_ends_at' => $stripeSubscription->current_period_end,
                'canceled_at' => $stripeSubscription->canceled_at,
                'ended_at' => $stripeSubscription->ended_at,
                'status' => $stripeSubscription->status,
                'last_stripe_event_at' => $stripeEvent->created,
            ], $subscription);
        });

        ProcessStripeSubscriptionWebhookEvent::dispatch($stripeEvent, $this->marketId);
    }
}
