<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Classroom;
use App\Models\Users\Teacher;
use App\Services\StudentService;
use Illuminate\Http\Request;

class ClassroomStudentController extends Controller
{
    public function __construct(
        protected StudentService $studentService,
    )
    {
    }

    public function index(Request $request, Classroom $classroom)
    {
        $this->authorize('viewAnyStudent', $classroom);

        $authenticatedUser = $request->user();

        if ($authenticatedUser->isTeacher()) {
            /** @var Teacher $authenticatedTeacher */
            $authenticatedTeacher = $authenticatedUser->asTeacher();

            $options = [
                'school_id' => $authenticatedTeacher->school_id,
                'pagination' => true,
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 20),
                'with_activities' => true,
                'with_classroom_groups' => true,
                'with_classrooms' => true,
                'classroom_ids' => [$classroom->id],
            ];

            $students = $this->studentService->search($options);

            return $this->successResponse(StudentResource::collection($students));
        }

        return $this->errorResponse(message: 'Failed to get students of the classroom.');
    }
}
