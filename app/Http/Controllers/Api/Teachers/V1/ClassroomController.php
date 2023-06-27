<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Services\AuthService;
use App\Services\ClassroomService;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function __construct(
        protected ClassroomService $classroomService,
        protected AuthService      $authService,
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Classroom::class);

        $authenticatedTeacher = $this->authService->teacher();

        $classrooms = $this->classroomService->search([
            'school_id' => $authenticatedTeacher->school_id,
            'key' => $request->input('search_key'),
        ]);

        return ClassroomResource::collection($classrooms);
    }
}
