<?php

namespace App\Services;

use App\Enums\ActionTypes;
use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Carbon\Carbon;

class ActionService
{
    /**
     * Store the action of given actor into database.
     *
     * @param Teacher|Student|Admin|Developer|null $actor
     * @param ActionTypes $action
     * @param Carbon|null $actedAt
     * @param array|null $data
     * @return void
     */
    public function create(Teacher|Student|Admin|Developer|null $actor, ActionTypes $action, Carbon $actedAt = null, array $data = null): void
    {
        $actor?->actions()->create([
            'action' => $action,
            'data' => $data,
            'acted_at' => $actedAt ?? Carbon::now(),
        ]);
    }
}
