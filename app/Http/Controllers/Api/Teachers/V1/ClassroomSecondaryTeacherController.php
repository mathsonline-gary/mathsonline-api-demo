<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Classroom;
use App\Models\Users\Teacher;
use App\Services\ClassroomService;

class ClassroomSecondaryTeacherController extends Controller
{
    public function __construct(
        public ClassroomService $classroomService,
    )
    {
    }

    public function store(Classroom $classroom, Teacher $teacher)
    {
        $this->authorize('addSecondaryTeacher', [$classroom, $teacher]);

        // Validate whether the teacher is already the secondary teacher of the classroom.
        if ($teacher->isSecondaryTeacherOfClassroom($classroom)) {
            return response()->json([
                'message' => 'The teacher is already the secondary teacher of the classroom.',
            ], 422);
        }

        // Validate whether the teacher is already the owner of the classroom.
        if ($teacher->isOwnerOfClassroom($classroom)) {
            return response()->json([
                'message' => 'The teacher is the owner of the classroom.',
            ], 422);
        }

        $this->classroomService->addSecondaryTeachers($classroom, [$teacher->id], false);

        return response()->json([
            'message' => 'Secondary teacher added successfully.',
        ], 201);
    }
}
