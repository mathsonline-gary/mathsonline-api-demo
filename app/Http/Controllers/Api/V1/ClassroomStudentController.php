<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Classroom;
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

        $options = [
            'school_id' => $classroom->school_id,
            'pagination' => true,
            'page' => $request->integer('page', 1),
            'per_page' => $request->integer('per_page', 20),
            'classroom_ids' => [$classroom->id],
            'with_classroom_groups' => true,
            'with_activities' => true,
        ];

        $students = $this->studentService->search($options);

        return $this->successResponse(StudentResource::collection($students));
    }
}
