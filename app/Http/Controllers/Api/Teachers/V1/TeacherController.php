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
        return response('teachers.index');
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
