<?php

use App\Models\Subscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $status = [
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_INCOMPLETE,
                Subscription::STATUS_INCOMPLETE_EXPIRED,
                Subscription::STATUS_PAST_DUE,
                Subscription::STATUS_CANCELED,
                Subscription::STATUS_UNPAID,
                Subscription::STATUS_TRIALING,
                Subscription::STATUS_PAUSED,
                Subscription::STATUS_UNKNOWN,
            ];

            $table->id();

            $table->foreignId('membership_id')
                ->nullable()
                ->constrained();

            $table->foreignId('school_id')
                ->constrained();

            $table->string('stripe_id')
                ->unique();

            $table->timestamp('starts_at')
                ->comment('The start date of the subscription.');

            $table->timestamp('cancels_at')
                ->nullable()
                ->comment('A date in the future at which the subscription will automatically get canceled. Null if the subscription is an active monthly subscription.');

            $table->timestamp('current_period_starts_at')
                ->nullable()
                ->comment('Start of the current period that the subscription has been invoiced for.');

            $table->timestamp('current_period_ends_at')
                ->nullable()
                ->comment('End of the current period that the subscription has been invoiced for. At the end of this period, a new invoice will be created for a monthly subscription.');

            $table->timestamp('canceled_at')
                ->nullable()
                ->comment('The date at which the subscription was actually canceled.');

            $table->timestamp('ended_at')
                ->nullable()
                ->comment('The date when the subscription actually ended. Null if the subscription has not ended.');

            $table->enum('status', $status)
                ->comment('The status of the subscription.');

            $table->unsignedInteger('custom_user_limit')
                ->nullable()
                ->comment('The custom user limit of the subscription. Null if the user limit is same as the membership user limit.');

            $table->timestamp('last_stripe_event_at')
                ->nullable()
                ->comment(
                    'The creation date of the last handled Stripe event that made changes on the record. Null if no event has been handled yet. ' .
                    'This field is used to avoid unexpected errors caused by incorrect order of events delivery.'
                );

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
