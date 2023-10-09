<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ClassroomType;
use App\Events\Classroom\ClassroomCreated;
use App\Events\Classroom\ClassroomDeleted;
use App\Events\Classroom\ClassroomGroupCreated;
use App\Events\Classroom\ClassroomUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Classroom\StoreClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Services\AuthService;
use App\Services\ClassroomService;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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

        $authenticatedUser = $request->user();

        $options = [
            'search_key' => $request->input('search_key'),
            'pagination' => $request->boolean('pagination', true),
            'with_owner' => $request->boolean('with_owner'),
            'with_secondary_teachers' => $request->boolean('with_secondary_teachers'),
            'with_groups' => $request->boolean('with_groups'),
        ];

        if ($authenticatedTeacher = $authenticatedUser->asTeacher()) {
            $options['school_id'] = $authenticatedTeacher->school_id;

            if (!$authenticatedTeacher->isAdmin()) {
                $options['owner_id'] = $authenticatedTeacher->id;
            }
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
            'mastery_enabled',
            'self_rating_enabled',
            'groups',
        ]);

        if ($authenticatedTeacher = $authenticatedUser->asTeacher()) {
            $attributes['school_id'] = $authenticatedTeacher->school_id;
            $attributes['type'] = ClassroomType::TRADITIONAL_CLASSROOM;
        }

        return DB::transaction(function () use ($attributes, $authenticatedUser) {
            // Create the classroom.
            $classroom = $this->classroomService->create($attributes);

            // Dispatch ClassroomCreated event.
            ClassroomCreated::dispatch($authenticatedUser, $classroom);

            // Add custom groups if any.
            if (isset($attributes['groups']) && count($attributes['groups']) > 0) {
                foreach ($attributes['groups'] as $group) {
                    $classroomGroup = $this->classroomService->addCustomGroup($classroom, $group);

                    ClassroomGroupCreated::dispatch($authenticatedUser, $classroomGroup);
                }
            }

            // Add secondary teachers if it is passed.
            if (isset($attributes['secondary_teacher_ids']) && count($attributes['secondary_teacher_ids']) > 0) {
                $this->classroomService->assignSecondaryTeachers($classroom, $attributes['secondary_teacher_ids']);
            }

            return response()->json(new ClassroomResource($classroom), 201);
        });
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

    public function destroy(Request $request, Classroom $classroom)
    {
        $this->authorize('delete', $classroom);

        $this->classroomService->delete($classroom);

        ClassroomDeleted::dispatch($request->user(), $classroom);

        return response()->noContent();
    }
}
