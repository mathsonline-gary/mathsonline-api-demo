<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Events\Classrooms\ClassroomCreated;
use App\Events\Classrooms\ClassroomDeleted;
use App\Events\Classrooms\ClassroomUpdated;
use App\Http\Requests\Classrooms\StoreClassroomRequest;
use App\Http\Requests\Classrooms\UpdateClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Services\AuthService;
use App\Services\ClassroomService;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

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
            'secondary_teacher_ids',
            'groups',
        ]);

        // Authorize.
        $owner = $this->teacherService->find($attributes['owner_id']);
        $this->authorize('create', [Classroom::class, $owner]);

        $authenticatedTeacher = $this->authService->teacher();

        $attributes['school_id'] = $authenticatedTeacher->school_id;
        $attributes['type'] = Classroom::TRADITIONAL_CLASSROOM;

        $classroom = $this->classroomService->create($attributes);

        ClassroomCreated::dispatch($authenticatedTeacher, $classroom);

        return response()->json(new ClassroomResource($classroom), 201);
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        $this->authorize('update', $classroom);

        $attributes = $request->safe()->only([
            'name',
            'owner_id',
            'pass_grade',
            'attempts',
        ]);

        $authenticatedTeacher = $this->authService->teacher();

        // Prevent non-admin teachers from changing the classroom owner.
        if (!$authenticatedTeacher->isAdmin()) {
            $attributes = Arr::except($attributes, 'owner_id');
        }

        $beforeAttributes = $classroom->getAttributes();

        $updatedClassroom = $this->classroomService->update($classroom, $attributes);

        ClassroomUpdated::dispatch($authenticatedTeacher, $beforeAttributes, $updatedClassroom);

        return response()->json(new ClassroomResource($updatedClassroom));
    }

    public function destroy(Classroom $classroom)
    {
        $this->authorize('delete', $classroom);

        $this->classroomService->delete($classroom);

        ClassroomDeleted::dispatch($this->authService->teacher(), $classroom);

        return response()->noContent();
    }
}
