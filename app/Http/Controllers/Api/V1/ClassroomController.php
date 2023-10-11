<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ClassroomType;
use App\Events\Classroom\ClassroomCreated;
use App\Events\Classroom\ClassroomDeleted;
use App\Events\Classroom\ClassroomGroupCreated;
use App\Events\Classroom\ClassroomUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Classroom\StoreClassroomRequest;
use App\Http\Requests\Classroom\UpdateClassroomRequest;
use App\Http\Resources\ClassroomResource;
use App\Models\Classroom;
use App\Services\AuthService;
use App\Services\ClassroomService;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'per_page' => $request->integer('per_page', 20),
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

        $classroom = $this->classroomService->find($classroom->id, [
            'with_owner' => true,
            'with_secondary_teachers' => true,
            'with_custom_groups' => true,
        ]);

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

            return response()->json([
                'message' => 'The classroom was created successfully.',
                'data' => new ClassroomResource($classroom),
            ], 201);
        });
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        // Authorize request.
        $this->authorize('update', $classroom);

        $validated = $request->safe()->only([
            'name',
            'year_id',
            'owner_id',
            'pass_grade',
            'attempts',
            'mastery_enabled',
            'self_rating_enabled',
        ]);

        $updatedClassroom = $this->classroomService->update($classroom, $validated);

        ClassroomUpdated::dispatch($request->user(), $validated, $updatedClassroom);

        return response()->json([
            'message' => 'The classroom was updated successfully.',
            'data' => new ClassroomResource($updatedClassroom->load('owner')),
        ]);
    }

    public function destroy(Request $request, Classroom $classroom)
    {
        $this->authorize('delete', $classroom);

        $this->classroomService->delete($classroom);

        ClassroomDeleted::dispatch($request->user(), $classroom);

        return response()->json([
            'message' => 'The classroom was deleted successfully.',
        ]);
    }
}
