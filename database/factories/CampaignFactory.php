<?php

namespace Database\Factories;

use App\Models\Campaign;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CampaignFactory extends Factory
{
    protected $model = Campaign::class;

    public function definition(): array
    {
        return [
            'code' => fake()->uuid,
            'description' => fake()->text,
            'expires_at' => fake()->optional()->dateTimeBetween(Carbon::now()->subYear(), Carbon::now()->addYear()),
        ];
    }

    /**
     * Indicate that the campaign has expired.
     *
     * @return Factory
     */
    public function expired(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => fake()->dateTimeBetween(Carbon::now()->subYear(), Carbon::now()->subDay()),
            ];
        });
    }

    /**
     * Indicate that the campaign has not expired.
     *
     * @return Factory
     */
    public function active(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => fake()->optional()->dateTimeBetween(Carbon::now()->addDay(), Carbon::now()->addYear()),
            ];
        });
    }
}
