<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Users\Student;
use App\Models\Users\Teacher;

class ActivityService
{
    /**
     * Store the activity of given actor into database.
     *
     * @param Teacher|Student|null $actor
     * @param string $action
     * @param array $data
     * @return void
     */
    public function create(Teacher|Student|null $actor, string $action, array $data = []): void
    {
        if ($actor && in_array($action, Activity::getActions())) {
            $actor->activities()->create([
                'action' => $action,
                'data' => $data,
            ]);
        }
    }
}
