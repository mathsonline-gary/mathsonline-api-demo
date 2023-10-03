<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\MaxClassroomGroupCountReachedException;
use App\Http\Controllers\Api\Controller;
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
            'attempts' => ['required', 'integer', 'min:1'],
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

    public function update(Request $request, Classroom $classroom, ClassroomGroup $classroomGroup)
    {
        $this->authorize('update', [$classroomGroup, $classroom]);

        // Validate the request data.
        $validated = $request->validate([
            'name' => ['string', 'max:255'],
            'pass_grade' => ['integer', 'min:0', 'max:100'],
            'attempts' => ['integer', 'min:1'],
        ]);

        // Update the classroom group.
        $this->classroomService->updateGroup($classroomGroup, $validated);

        return response()->json([
            'message' => 'The classroom group was updated successfully.',
            'data' => $classroomGroup,
        ]);
    }

    public function destroy(Classroom $classroom, ClassroomGroup $classroomGroup)
    {
        $this->authorize('delete', [$classroomGroup, $classroom]);

        // Delete the classroom group.
        $this->classroomService->deleteGroup($classroomGroup);

        return response()->noContent();
    }
}
