<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Carbon\Carbon;

class ActivityService
{
    /**
     * Store the activity of given actor into database.
     *
     * @param Teacher|Student|Admin|Developer|null $actor
     * @param string $action
     * @param Carbon|null $actedAt
     * @param array|null $data
     * @return void
     */
    public function create(Teacher|Student|Admin|Developer|null $actor, string $action, Carbon $actedAt = null, array $data = null): void
    {
        if ($actor && in_array($action, Activity::getActions())) {
            $actor->activities()->create([
                'action' => $action,
                'data' => $data,
                'acted_at' => $actedAt ?? Carbon::now(),
            ]);
        }
    }
}
