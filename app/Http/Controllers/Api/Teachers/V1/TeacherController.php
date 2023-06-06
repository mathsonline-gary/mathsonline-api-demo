<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Http\Resources\TeacherResource;
use App\Models\Users\Teacher;
use App\Services\TeacherService;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService
    )
    {
    }

    public function index(Request $request)
    {
        /* @var Teacher $teacher */
        $teacher = $request->user();

        $this->authorize('viewAnyInSchool', [Teacher::class, $teacher->school_id]);

        $teachers = $this->teacherService->search([
            'school_id' => $teacher->school_id,
        ]);

        return TeacherResource::collection($teachers);
    }

    public function show(Request $request, Teacher $teacher)
    {
        $this->authorize('view', $teacher);

        $teacher = $this->teacherService->find($teacher->id, [
            'with_school' => true,
            'with_classrooms' => true,
        ]);

        return new TeacherResource($teacher);
    }
}
