<?php

namespace Tests\Traits;

use App\Models\Action;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;

trait ActionHelpers
{
    /**
     * Create action(s) for given user.
     *
     * @param User $actor
     * @param int $count
     * @param array $attributes
     * @return Collection|Action
     */
    public function createAction(User $actor, int $count = 1, array $attributes = []): Collection|Action
    {
        $actions = Action::factory()
            ->count($count)
            ->for($actor, 'actionable')
            ->create($attributes);

        return $count === 1 ? $actions->first() : $actions;
    }
}
