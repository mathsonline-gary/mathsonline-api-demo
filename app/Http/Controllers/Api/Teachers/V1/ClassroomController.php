<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Events\Classrooms\ClassroomCreated;
use App\Events\Classrooms\ClassroomDeleted;
use App\Events\Classrooms\ClassroomUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Services\AuthService;
use App\Services\ClassroomService;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        // Authorize.
        $this->authorize('create', Classroom::class);

        $authenticatedTeacher = $this->authService->teacher();

        // Validate request.
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:32',
            ],
            'owner_id' => [
                'required',
                'int',
                $authenticatedTeacher->isAdmin()
                    ? Rule::exists('teachers', 'id')
                    ->where('school_id', $authenticatedTeacher->school_id)  // Admin teacher can create classroom for any teacher in the school.
                    : Rule::exists('teachers', 'id')
                    ->where('id', $authenticatedTeacher->id),   // Non-admin teacher can only create classroom for himself.
            ],
            'pass_grade' => [
                'required',
                'int',
                'min:0',
                'max:100',
            ],
            'attempts' => [
                'required',
                'int',
                'min:1',
            ],
            'secondary_teacher_ids' => ['array'],
            'secondary_teacher_ids.*' => [
                'required',
                'int',
                Rule::exists('teachers', 'id')
                    ->where('school_id', $authenticatedTeacher->school_id), // Can only add secondary teacher in the same school.
            ],
            'groups' => [
                'array',
                'max:8'
            ],
            'groups.*.name' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'groups.*.pass_grade' => [
                'required',
                'int',
                'min:0',
                'max:100',
            ],
            'groups.*.attempts' => [
                'required',
                'int',
                'min:1',
            ],
        ]);

        // Construct attributes.
        $attributes = Arr::only($validated, [
            'name',
            'owner_id',
            'pass_grade',
            'attempts',
            'secondary_teacher_ids',
            'groups',
        ]);
        $attributes['school_id'] = $authenticatedTeacher->school_id;
        $attributes['type'] = Classroom::TRADITIONAL_CLASSROOM;

        // Create the classroom.
        $classroom = $this->classroomService->create($attributes);

        // Dispatch ClassroomCreated event.
        ClassroomCreated::dispatch($authenticatedTeacher, $classroom);

        return response()->json(new ClassroomResource($classroom), 201);
    }

    public function update(Request $request, Classroom $classroom)
    {
        // Authorize request.
        $this->authorize('update', $classroom);

        $authenticatedTeacher = $this->authService->teacher();

        // Validate request.
        $validated = $request->validate([
            'name' => [
                'string',
                'max:32',
            ],
            'owner_id' => [
                'int',
                $authenticatedTeacher->isAdmin()
                    ? Rule::exists('teachers', 'id')
                    ->where('school_id', $classroom->school_id) // Admin teacher can update owner to a teacher in the same school.
                    : Rule::exists('teachers', 'id')
                    ->where('id', $authenticatedTeacher->id),   // Non-admin teacher can only arrange the owner to himself.
            ],
            'pass_grade' => [
                'int',
                'min:0',
                'max:100',
            ],
            'attempts' => [
                'int',
                'min:1',
            ],
        ]);

        // Get valid request data.
        $attributes = Arr::only($validated, [
            'name',
            'owner_id',
            'pass_grade',
            'attempts',
        ]);

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
