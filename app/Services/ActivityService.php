<?php

namespace App\Services;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Users\Admin;
use App\Models\Users\Developer;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    /**
     * Save the activity of given actor into database.
     *
     * @param User|null $actor
     * @param ActivityTypes $type
     * @param Carbon|null $actedAt
     * @param array|null $data
     * @return Activity|null
     */
    public function create(User|null $actor, ActivityTypes $type, Carbon $actedAt = null, array $data = null): ?Activity
    {
        return $actor?->activities()->create([
            'type' => $type,
            'data' => $data,
            'acted_at' => $actedAt ?? Carbon::now(),
        ]);
    }
}
