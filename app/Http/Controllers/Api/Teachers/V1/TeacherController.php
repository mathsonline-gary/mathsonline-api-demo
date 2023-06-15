<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Events\Teachers\TeacherCreated;
use App\Http\Requests\Teachers\IndexTeacherRequest;
use App\Http\Requests\Teachers\StoreTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Users\Teacher;
use App\Services\AuthService;
use App\Services\TeacherService;

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
        $user = $this->authService->teacher();

        $teachers = $this->teacherService->search([
            'school_id' => $user->school_id,
            'key' => $request->input('search'),
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
        $user = $this->authService->teacher();

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

        $teacher = $this->teacherService->create([
            ...$validated,
            'school_id' => $user->school_id,
        ]);

        TeacherCreated::dispatch($user, $teacher);

        return response()->json(new TeacherResource($teacher), 201);
    }

    public function destroy(Teacher $teacher)
    {
        $this->authorize('delete', $teacher);

        $this->teacherService->delete($teacher);

        return response()->noContent();
    }
}

