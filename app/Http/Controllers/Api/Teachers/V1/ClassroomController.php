<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Events\Classrooms\ClassroomDeleted;
use App\Http\Requests\Classrooms\StoreClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Services\AuthService;
use App\Services\ClassroomService;
use App\Services\TeacherService;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function __construct(
        protected ClassroomService $classroomService,
        protected AuthService      $authService,
        protected TeacherService   $teacherService,
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Classroom::class);

        $authenticatedTeacher = $this->authService->teacher();

        $options = [
            'school_id' => $authenticatedTeacher->school_id,
            'key' => $request->input('search_key'),
        ];

        if (!$authenticatedTeacher->isAdmin()) {
            $options['owner_id'] = $authenticatedTeacher->id;
        }

        $classrooms = $this->classroomService->search($options);

        return ClassroomResource::collection($classrooms);
    }

    public function show(Classroom $classroom)
    {
        $this->authorize('view', $classroom);

        $classroom = $this->classroomService->find($classroom->id);

        return new ClassroomResource($classroom);
    }

    public function store(StoreClassroomRequest $request)
    {
        $attributes = $request->safe()->only([
            'name',
            'owner_id',
            'pass_grade',
            'attempts',
        ]);

        // Authorize.
        $owner = $this->teacherService->find($attributes['owner_id']);
        $this->authorize('create', [Classroom::class, $owner]);

        $authenticatedTeacher = $this->authService->teacher();

        $attributes['school_id'] = $authenticatedTeacher->school_id;
        $attributes['type'] = Classroom::TRADITIONAL_CLASSROOM;
        
        $classroom = $this->classroomService->create($attributes);

        return response()->json(new ClassroomResource($classroom), 201);
    }

    public function destroy(Classroom $classroom)
    {
        $this->authorize('delete', $classroom);

        $this->classroomService->delete($classroom);

        ClassroomDeleted::dispatch($this->authService->teacher(), $classroom);

        return response()->noContent();
    }
}
