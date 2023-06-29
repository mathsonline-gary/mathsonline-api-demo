<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Events\Teachers\TeacherCreated;
use App\Events\Teachers\TeacherDeleted;
use App\Events\Teachers\TeacherUpdated;
use App\Http\Requests\Teachers\IndexTeacherRequest;
use App\Http\Requests\Teachers\StoreTeacherRequest;
use App\Http\Requests\Teachers\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Users\Teacher;
use App\Services\AuthService;
use App\Services\TeacherService;
use Illuminate\Support\Arr;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService,
        protected AuthService    $authService,
    )
    {
    }

    public function index(IndexTeacherRequest $request)
    {
        $this->authorize('viewAny', Teacher::class);

        $user = $this->authService->teacher();

        $teachers = $this->teacherService->search([
            'school_id' => $user->school_id,
            'key' => $request->input('search_key'),
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

    public function store(StoreTeacherRequest $request)
    {
        $authenticatedTeacher = $this->authService->teacher();

        $attributes = $request->safe()->only([
            'username',
            'email',
            'password',
            'first_name',
            'last_name',
            'title',
            'position',
            'is_admin',
        ]);

        $teacher = $this->teacherService->create([
            ...$attributes,
            'school_id' => $authenticatedTeacher->school_id,
        ]);

        TeacherCreated::dispatch($this->authService->teacher(), $teacher);

        return response()->json(new TeacherResource($teacher), 201);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $this->authorize('update', $teacher);

        $attributes = $request->safe()->only([
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
            $attributes = Arr::except($attributes, 'is_admin');
        }

        $beforeAttributes = $teacher->getAttributes();

        $updatedTeacher = $this->teacherService->update($teacher, $attributes);

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

