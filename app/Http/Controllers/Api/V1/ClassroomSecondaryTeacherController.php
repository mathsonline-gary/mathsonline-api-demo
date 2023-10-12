<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Classroom\AddSecondaryTeacherRequest;
use App\Models\Classroom;
use App\Models\Users\Teacher;
use App\Services\ClassroomService;
use App\Services\TeacherService;

class ClassroomSecondaryTeacherController extends Controller
{
    public function __construct(
        protected ClassroomService $classroomService,
        protected TeacherService   $teacherService,
    )
    {
    }

    public function store(AddSecondaryTeacherRequest $request, Classroom $classroom)
    {
        $this->authorize('addSecondaryTeacher', $classroom);

        $teacher = $this->teacherService->findByUserId($request->integer('user_id'));

        // Validate whether the teacher is already the secondary teacher of the classroom.
        if ($teacher->isSecondaryTeacherOfClassroom($classroom)) {
            return $this->errorResponse(
                null,
                'The teacher is already the secondary teacher of the classroom.',
                422,
            );
        }

        // Validate whether the teacher is already the owner of the classroom.
        if ($teacher->isOwnerOfClassroom($classroom)) {
            return $this->errorResponse(
                null,
                'The teacher is the owner of the classroom.',
                422,
            );
        }

        $this->classroomService->assignSecondaryTeachers($classroom, [$teacher->id], false);

        return $this->successResponse(
            null,
            'The teacher was added as the secondary teacher of the classroom successfully.',
            201,
        );
    }

    public function destroy(Classroom $classroom, Teacher $teacher)
    {
        $this->authorize('removeSecondaryTeacher', [$classroom, $teacher]);

        // Validate whether the teacher is the secondary teacher of the classroom.
        if (!$teacher->isSecondaryTeacherOfClassroom($classroom)) {
            return response()->json([
                'message' => 'The teacher is not the secondary teacher of the classroom.',
            ], 422);
        }

        $this->classroomService->removeSecondaryTeachers($classroom, [$teacher->id]);

        return response()->noContent();
    }
}
