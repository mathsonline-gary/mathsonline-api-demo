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

        return $this->successResponse(ClassroomResource::collection($classrooms));
    }

    public function show(Classroom $classroom)
    {
        $this->authorize('view', $classroom);

        $classroom = $this->classroomService->find($classroom->id, [
            'with_owner' => true,
            'with_secondary_teachers' => true,
            'with_custom_groups' => true,
        ]);

        return $this->successResponse(new ClassroomResource($classroom));
    }

    public function store(StoreClassroomRequest $request)
    {
        $this->authorize('create', Classroom::class);
        $authenticatedUser = $request->user();

        // Construct attributes.
        $validated = $request->only([
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

        // Set school_id and classroom type if the authenticated user is a teacher.
        if ($authenticatedTeacher = $authenticatedUser->asTeacher()) {
            $validated['school_id'] = $authenticatedTeacher->school_id;
            $validated['type'] = ClassroomType::TRADITIONAL_CLASSROOM;
        }

        $classroom = DB::transaction(function () use ($validated, $authenticatedUser) {
            // Create the classroom.
            $classroom = $this->classroomService->create($validated);

            // Dispatch ClassroomCreated event.
            ClassroomCreated::dispatch($authenticatedUser, $classroom);

            // Add custom groups if any.
            if (isset($validated['groups']) && count($validated['groups']) > 0) {
                foreach ($validated['groups'] as $group) {
                    $classroomGroup = $this->classroomService->addCustomGroup($classroom, $group);

                    ClassroomGroupCreated::dispatch($authenticatedUser, $classroomGroup);
                }
            }

            // Add secondary teachers if it is passed.
            if (isset($validated['secondary_teacher_ids']) && count($validated['secondary_teacher_ids']) > 0) {
                $this->classroomService->assignSecondaryTeachers($classroom, $validated['secondary_teacher_ids']);
            }

            return $classroom;
        });

        return $this->successResponse(
            new ClassroomResource($classroom),
            'The classroom is created successfully.',
            201,
        );
    }

    public function update(UpdateClassroomRequest $request, Classroom $classroom)
    {
        // Authorize request.
        $this->authorize('update', $classroom);
        $authenticatedUser = $request->user();

        $validated = $request->safe()->only([
            'name',
            'year_id',
            'owner_id',
            'pass_grade',
            'attempts',
            'mastery_enabled',
            'self_rating_enabled',
            'secondary_teacher_ids',
        ]);

        $updatedClassroom = DB::transaction(function () use ($authenticatedUser, $classroom, $validated) {
            // Update the classroom.
            $updatedClassroom = $this->classroomService->update($classroom, $validated);

            // Update secondary teachers if it is passed.
            if (isset($validated['secondary_teacher_ids']) && count($validated['secondary_teacher_ids']) > 0) {
                $this->classroomService->assignSecondaryTeachers($classroom, $validated['secondary_teacher_ids']);
            }

            ClassroomUpdated::dispatch($authenticatedUser, $validated, $updatedClassroom);

            return $updatedClassroom;
        });

        return $this->successResponse(
            new ClassroomResource($updatedClassroom->load('owner')),
            'The classroom is updated successfully.',
        );
    }

    public function destroy(Request $request, Classroom $classroom)
    {
        $this->authorize('delete', $classroom);

        $this->classroomService->delete($classroom);

        ClassroomDeleted::dispatch($request->user(), $classroom);

        return $this->successResponse(null, 'The classroom was deleted successfully.');
    }
}
