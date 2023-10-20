<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\Student\StudentCreated;
use App\Events\Student\StudentDeleted;
use App\Events\Student\StudentUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Services\AuthService;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $authenticatedUser = $request->user();

        if ($authenticatedUser->isTeacher()) {
            /** @var Teacher $authenticatedTeacher */
            $authenticatedTeacher = $authenticatedUser->asTeacher();

            $options = [
                'school_id' => $authenticatedTeacher->school_id,
                'key' => $request->input('search_key'),
                'all' => $request->boolean('all', true),    // Whether to show all students or only those managed by the authenticated teacher.
                'pagination' => $request->boolean('pagination', false),
                'page' => $request->integer('page', 1),
                'per_page' => $request->integer('per_page', 20),
                'with_school' => $request->boolean('with_school', false),
                'with_activities' => $request->boolean('with_activities', false),
                'with_classroom_groups' => $request->boolean('with_classroom_groups', false),
            ];

            // If the authenticated user is not an admin teacher, or the request does not want to show all students,
            // then get IDs of classrooms of which the authenticated teacher is the owner or secondary teacher.
            if (!$authenticatedTeacher->isAdmin() || !$options['all']) {
                // Merge the two arrays of classroom IDs.
                $options['classroom_ids'] = $authenticatedTeacher->getOwnedAndSecondaryClassrooms()->pluck('id')->toArray();
            }

            $students = $this->studentService->search($options);

            return $this->successResponse(StudentResource::collection($students));
        }

        return $this->errorResponse(
            message: 'Failed to get the student list.',
        );
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $student = $this->studentService->find($student->id, [
            'with_school' => true,
            'with_classroom_groups' => true,
        ]);

        return new StudentResource($student);
    }

    public function store(StoreStudentRequest $request)
    {
        $this->authorize('create', Student::class);
        $authenticatedUser = $request->user();

        $validated = $request->safe()->only([
            'username',
            'email',
            'first_name',
            'last_name',
            'password',
        ]);

        if ($authenticatedUser->isTeacher()) {
            $authenticatedTeacher = $authenticatedUser->asTeacher();

            $validated['school_id'] = $authenticatedTeacher->school_id;
            $validated['settings'] = [
                'expired_tasks_excluded' => $request->boolean('expired_tasks_excluded', true),
                'confetti_enabled' => $request->boolean('confetti_enabled', true),
            ];
            $validated['classroom_group_ids'] = $request->input('classroom_group_ids', []);

            $student = DB::transaction(function () use ($validated, $authenticatedTeacher) {
                // Create a user.
                $student = $this->studentService->create($validated);

                // Assign the student into the given classroom groups.
                if (isset($validated['classroom_group_ids']) && count($validated['classroom_group_ids']) > 0) {
                    $this->studentService->assignToClassroomGroups($student, $validated['classroom_group_ids']);
                }

                return $student;
            });

            StudentCreated::dispatch($authenticatedUser, $student);

            return $this->successResponse(
                data: new StudentResource($student),
                message: 'The student is created successfully.',
                status: 201,
            );
        }

        return $this->errorResponse(
            message: 'Failed to create the student.',
        );
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->safe()->only([
            'username',
            'email',
            'first_name',
            'last_name',
            'password',
        ]);

        $beforeAttributes = $student->getAttributes();

        $updatedStudent = $this->studentService->update($student, $validated);

        StudentUpdated::dispatch($this->authService->teacher(), $beforeAttributes, $updatedStudent);

        return new StudentResource($updatedStudent);
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $this->studentService->delete($student);

        StudentDeleted::dispatch($this->authService->teacher(), $student);

        return response()->noContent();
    }
}
