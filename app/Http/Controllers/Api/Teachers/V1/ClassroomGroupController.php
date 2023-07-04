<?php

namespace App\Http\Controllers\Api\Teachers\V1;

use App\Exceptions\MaxClassroomGroupCountReachedException;
use App\Models\Classroom;
use App\Models\ClassroomGroup;
use App\Services\ClassroomService;
use Illuminate\Http\Request;

class ClassroomGroupController extends Controller
{
    public function __construct(
        protected ClassroomService $classroomService,
    )
    {
    }

    public function store(Request $request, Classroom $classroom)
    {
        $this->authorize('create', [ClassroomGroup::class, $classroom]);

        // Validate the request data.
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'pass_grade' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        // Create the classroom group in the classroom.
        try {
            $classroomGroup = $this->classroomService->addCustomGroup($classroom, $validated);

            return response()->json([
                'message' => 'The classroom group was created successfully.',
                'data' => $classroomGroup,
            ], 201);
        } catch (MaxClassroomGroupCountReachedException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
