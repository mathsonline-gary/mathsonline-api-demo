<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Events\Teachers\TeacherCreated;
use App\Events\Teachers\TeacherDeleted;
use App\Events\Teachers\TeacherUpdated;
use App\Http\Controllers\Api\Controller;
use App\Http\Requests\TeacherRequests\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Users\Teacher;
use App\Services\AuthService;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Password;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService,
        protected AuthService    $authService,
    )
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Teacher::class);

        $user = $this->authService->teacher();

        $teachers = $this->teacherService->search([
            'school_id' => $user->school_id,
            'key' => $request->input('search_key'),
            'pagination' => $request->boolean('pagination', true),
        ]);

        return TeacherResource::collection($teachers);
    }

    public function show(Teacher $teacher)
    {
        $this->authorize('view', $teacher);

        $teacher = $this->teacherService->find($teacher->id, [
            'with_school' => true,
            'with_classrooms' => true,
        ]);

        return new TeacherResource($teacher);
    }

    public function store(Request $request)
    {
        // Authorize the request.
        $this->authorize('create', Teacher::class);

        // Get the authenticated teacher.
        $authenticatedTeacher = $this->authService->teacher();

        $validated = $request->validate([
            'username' => ['required', 'string', 'unique:teachers'],
            'email' => ['nullable', 'email'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', Password::defaults()],
            'title' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['boolean'],
        ]);

        $attributes = [
            ...$validated,
            'school_id' => $authenticatedTeacher->school_id,
        ];

        $teacher = $this->teacherService->create($attributes);

        TeacherCreated::dispatch($authenticatedTeacher, $teacher);

        return response()->json(new TeacherResource($teacher), 201);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        // Authorize the request.
        $this->authorize('update', $teacher);

        // Get the validated attributes to update.
        $validated = $request->safe()->only([
            'username',
            'email',
            'password',
            'first_name',
            'last_name',
            'title',
            'position',
            'is_admin',
        ]);

        $authenticatedTeacher = $this->authService->teacher();

        // Prevent non-admin teachers from changing admin access.
        if (!$authenticatedTeacher->isAdmin()) {
            $validated = Arr::except($validated, 'is_admin');
        }

        // Prevent admin teachers from changing their own admin access.
        if ($authenticatedTeacher->isAdmin() && $authenticatedTeacher->id === $teacher->id) {
            $validated = Arr::except($validated, 'is_admin');
        }

        $beforeAttributes = $teacher->getAttributes();

        $updatedTeacher = $this->teacherService->update($teacher, $validated);

        TeacherUpdated::dispatch($authenticatedTeacher, $beforeAttributes, $updatedTeacher);

        return response()->json(new TeacherResource($updatedTeacher));
    }

    public function destroy(Teacher $teacher)
    {
        $this->authorize('delete', $teacher);

        $this->teacherService->delete($teacher);

        TeacherDeleted::dispatch($this->authService->teacher(), $teacher);

        return response()->noContent();
    }
}

