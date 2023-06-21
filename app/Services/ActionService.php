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
     * @param ActionTypes $type
     * @param Carbon|null $actedAt
     * @param array|null $data
     * @return Action|null
     */
    public function create(User|null $actor, ActionTypes $type, Carbon $actedAt = null, array $data = null): ?Action
    {
        return $actor?->actions()->create([
            'type' => $type,
            'data' => $data,
            'acted_at' => $actedAt ?? Carbon::now(),
        ]);
    }
}
