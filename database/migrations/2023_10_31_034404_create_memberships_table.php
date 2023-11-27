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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products');

            $table->foreignId('campaign_id')
                ->constrained('campaigns');

            $table->string('name')
                ->comment('The membership name.');

            $table->string('description')
                ->nullable()
                ->comment('The membership description.');

            $table->unsignedDecimal('price')
                ->comment('The membership price.');

            $table->unsignedTinyInteger('period_in_months')
                ->nullable()
                ->comment('The billing period in months.');

            $table->unsignedTinyInteger('period_in_days')
                ->nullable()
                ->comment('The billing period in days.');

            $table->unsignedInteger('iterations')
                ->nullable()
                ->default(null)
                ->comment('The multiplier applied to the membership interval. Null if the membership is recurring. For example, a membership with intervals = 12 and period_in_month = 1, results in a subscription of duration 12 * 1 = 12 months and charges monthly.');

            $table->unsignedTinyInteger('user_limit')
                ->default(1)
                ->comment('The user limit for the membership.');

            $table->string('stripe_id')
                ->unique()
                ->comment('The connected Stripe price ID.');

            $table->string('note')
                ->nullable()
                ->comment('The note tag that will be displayed on the membership.');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
