<?php

namespace Tests\Traits;

use App\Models\Activity;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;

trait TestActivityHelpers
{
    /**
     * Create fake activity(s) for given user.
     *
     * @param User $actor
     * @param int $count
     * @param array $attributes
     * @return Collection|Activity
     */
    public function fakeActivity(User $actor, int $count = 1, array $attributes = []): Collection|Activity
    {
        $activities = Activity::factory()
            ->count($count)
            ->for($actor, 'actable')
            ->create($attributes);

        return $count === 1 ? $activities->first() : $activities;
    }
}
