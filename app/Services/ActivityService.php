<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Users\User;
use Carbon\Carbon;

class ActivityService
{
    /**
     * Save the activity of given actor into database.
     *
     * @param User       $actor
     * @param int        $type
     * @param string     $description
     * @param Carbon     $actedAt
     * @param array|null $data
     *
     * @return Activity
     */
    public function create(User $actor, int $type, string $description, Carbon $actedAt, array $data = null): Activity
    {
        return $actor->activities()->create([
            'type' => $type,
            'description' => $description,
            'data' => $data,
            'acted_at' => $actedAt,
        ]);
    }
}
