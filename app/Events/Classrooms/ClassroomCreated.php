<?php

namespace App\Events\Classrooms;

use App\Enums\ActivityType;
use App\Events\ActivityLoggableEvent;
use App\Models\Classroom;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassroomCreated extends ActivityLoggableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Carbon $createdAt;

    /**
     * Create a new event instance.
     */
    public function __construct(User $creator, Classroom $classroom)
    {
        parent::__construct(
            actor: $creator,
            activityType: ActivityType::CREATED_CLASSROOM,
            actedAt: $classroom->created_at,
            data: [
                'classroom_id' => $classroom->id,
                'classroom_groups' => $classroom->customClassroomGroups->pluck('id'),
            ],
        );
    }
}
