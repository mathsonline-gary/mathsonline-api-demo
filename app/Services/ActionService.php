<?php

namespace App\Services;

use App\Enums\ActionTypes;
use App\Models\Action;
use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ActionService
{
    /**
     * Store the action of given actor into database.
     *
     * @param User|null $actor
     * @param ActionTypes $action
     * @param Carbon|null $actedAt
     * @param array|null $data
     * @return Action|null
     */
    public function create(User|null $actor, ActionTypes $action, Carbon $actedAt = null, array $data = null): ?Action
    {
        return $actor?->actions()->create([
            'action' => $action,
            'data' => $data,
            'acted_at' => $actedAt ?? Carbon::now(),
        ]);
    }
}
