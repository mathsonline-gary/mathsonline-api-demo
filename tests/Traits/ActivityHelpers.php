<?php

namespace Tests\Traits;

use App\Models\Activity;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;

trait ActivityHelpers
{
    /**
     * Create activity(s) for given user.
     *
     * @param User $actor
     * @param int $count
     * @param array $attributes
     * @return Collection|Activity
     */
    public function createAction(User $actor, int $count = 1, array $attributes = []): Collection|Activity
    {
        $activities = Activity::factory()
            ->count($count)
            ->for($actor, 'le')
            ->create($attributes);

        return $count === 1 ? $activities->first() : $activities;
    }
}
