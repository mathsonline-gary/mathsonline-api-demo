<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ClassroomType;
use App\Events\Classrooms\ClassroomCreated;
use App\Events\Classrooms\ClassroomDeleted;
use App\Events\Classrooms\ClassroomUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Classroom\StoreClassroomRequest;
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
            'pagination' => $request->boolean('pagination', true),
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
        // Authorize.
        $this->authorize('create', Classroom::class);
        $authenticatedUser = $request->user();

        // Construct attributes.
        $attributes = $request->only([
            'name',
            'year_id',
            'owner_id',
            'pass_grade',
            'attempts',
            'secondary_teacher_ids',
            'groups',
        ]);

        if ($authenticatedTeacher = $authenticatedUser->asTeacher()) {
            $attributes['school_id'] = $authenticatedTeacher->school_id;
            $attributes['type'] = ClassroomType::TRADITIONAL_CLASSROOM;
        }

        // Create the classroom.
        $classroom = $this->classroomService->create($attributes);

        // Dispatch ClassroomCreated event.
        ClassroomCreated::dispatch($authenticatedUser, $classroom);

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
