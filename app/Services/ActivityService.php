<?php

namespace App\Services;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Users\User;
use Carbon\Carbon;

class ActivityService
{
    /**
     * Save the activity of given actor into database.
     *
     * @param User $actor
     * @param ActivityTypes $type
     * @param Carbon $actedAt
     * @param array|null $data
     * @return Activity
     */
    public function create(User $actor, ActivityTypes $type, Carbon $actedAt, array $data = null): Activity
    {
        return $actor->activities()->create([
            'type' => $type,
            'data' => $data,
            'acted_at' => $actedAt,
        ]);
    }
}
