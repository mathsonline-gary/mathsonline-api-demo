<?php

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
            $table->id();

            $table->foreignId('product_id')
                ->constrained();

            $table->foreignId('membership_id')
                ->nullable()
                ->constrained();

            $table->foreignId('school_id')
                ->constrained();

            $table->string('stripe_subscription_id')
                ->unique();

            $table->timestamp('starts_at')
                ->comment('The start date of the subscription.');

            $table->timestamp('cancels_at')
                ->nullable()
                ->comment('A date in the future at which the subscription will automatically get canceled. Null if the subscription is an active monthly subscription.');

            $table->timestamp('canceled_at')
                ->nullable()
                ->comment('The date at which the subscription was actually canceled.');

            $table->timestamp('ended_at')
                ->nullable()
                ->comment('The date when the subscription actually ended. Null if the subscription is still active.');

            $table->string('status')
                ->comment('The status of the subscription.');

            $table->unsignedDecimal('custom_price')
                ->nullable()
                ->comment('The custom price of the subscription. Null if the price is same as the membership price.');

            $table->unsignedInteger('custom_user_limit')
                ->nullable()
                ->comment('The custom user limit of the subscription. Null if the user limit is same as the membership user limit.');

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
