<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Users\Student;
use App\Services\AuthService;
use App\Services\StudentService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        protected AuthService    $authService,
        protected StudentService $studentService,
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);

        $authenticatedTeacher = $this->authService->teacher();

        $options = [
            'school_id' => $authenticatedTeacher->school_id,
            'key' => $request->input('search_key'),
            'pagination' => $request->boolean('pagination', true),
        ];

        if (!$authenticatedTeacher->isAdmin()) {
            // Get IDs of classrooms owned by the authenticated teacher.
            $primaryClassroomIds = $authenticatedTeacher->ownedClassrooms()
                ->pluck('id')
                ->toArray();

            // Get IDs of classrooms where the authenticated teacher is a secondary teacher.
            $secondaryClassroomIds = $authenticatedTeacher->secondaryClassrooms()
                ->pluck('classrooms.id')
                ->toArray();

            // Merge the two arrays of classroom IDs.
            $options['classroom_ids'] = array_unique([...$primaryClassroomIds, ...$secondaryClassroomIds]);
        }

        $students = $this->studentService->search($options);

        return StudentResource::collection($students);
    }
}
