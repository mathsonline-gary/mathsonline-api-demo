<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\Students\StudentCreated;
use App\Events\Students\StudentDeleted;
use App\Events\Students\StudentUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
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
            'all' => $request->boolean('all', true),    // Whether to show all students or only those managed by the authenticated teacher.
        ];

        // Get IDs of classrooms managed by the authenticated teacher.
        if (!$authenticatedTeacher->isAdmin() || !$options['all']) {
            // Merge the two arrays of classroom IDs.
            $options['classroom_ids'] = $authenticatedTeacher->getOwnedAndSecondaryClassrooms()->pluck('id')->toArray();
        }

        $students = $this->studentService->search($options);

        return StudentResource::collection($students);
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

        $attributes = $request->safe()->only([
            'username',
            'email',
            'first_name',
            'last_name',
            'password',
        ]);

        $authenticatedTeacher = $this->authService->teacher();

        $attributes['school_id'] = $authenticatedTeacher->school_id;

        $student = $this->studentService->create($attributes);

        StudentCreated::dispatch($authenticatedTeacher, $student);

        return new StudentResource($student);
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
