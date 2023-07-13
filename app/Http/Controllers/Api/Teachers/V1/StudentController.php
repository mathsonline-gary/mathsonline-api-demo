<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Events\Students\StudentUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\StudentResource;
use App\Models\Users\Student;
use App\Services\AuthService;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

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

    public function update(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $payload = $request->validate([
            'username' => ['string', Rule::unique('students')->ignore($student->id)],
            'email' => ['nullable', 'email'],
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'password' => ['string', Password::defaults()],
        ]);

        $beforeAttributes = $student->getAttributes();

        $updatedStudent = $this->studentService->update($student, $payload);

        StudentUpdated::dispatch($this->authService->teacher(), $beforeAttributes, $updatedStudent);

        return new StudentResource($updatedStudent);
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);

        $this->studentService->softDelete($student);

        return response()->noContent();
    }
}
