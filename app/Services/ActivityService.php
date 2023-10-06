<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Users\User;
use Carbon\Carbon;

class ActivityService
{
    /**
     * Save the activity of given actor into database.
     *
     * @param User $actor
     * @param ActivityType $type
     * @param Carbon $actedAt
     * @param array|null $data
     * @return Activity
     */
    public function create(User $actor, ActivityType $type, Carbon $actedAt, array $data = null): Activity
    {
        return $actor->activities()->create([
            'type' => $type,
            'data' => $data,
            'acted_at' => $actedAt,
        ]);
    }
}
