<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Classroom\UpdateClassroomGroupRequest;
use App\Models\Classroom;
use App\Services\ClassroomGroupService;
use App\Services\ClassroomService;
use Illuminate\Support\Facades\DB;

class ClassroomGroupController extends Controller
{
    public function __construct(
        protected ClassroomService      $classroomService,
        protected ClassroomGroupService $classroomGroupService,
    )
    {
    }

    public function update(UpdateClassroomGroupRequest $request, Classroom $classroom)
    {
        $this->authorize('update', [$classroom]);

        $validated = $request->safe()->only([
            'adds',
            'removes',
            'updates',
        ]);

        DB::transaction(function () use ($validated, $classroom) {
            // Add groups if any.
            if (isset($validated['adds']) && count($validated['adds']) > 0) {
                foreach ($validated['adds'] as $attributes) {
                    $this->classroomGroupService->createCustom($classroom, $attributes);
                }
            }

            // Remove groups if any.
            if (isset($validated['removes']) && count($validated['removes']) > 0) {
                // Find the groups to remove.
                $groupsToRemove = $classroom->customClassroomGroups()
                    ->whereIn('id', $validated['removes'])
                    ->get();

                foreach ($groupsToRemove as $group) {
                    $this->classroomGroupService->deleteCustom($group);
                }
            }

            // Update groups if any.
            if (isset($validated['updates']) && count($validated['updates']) > 0) {
                $collection = collect($validated['updates']);

                // Find the groups to update.
                $groupsToUpdate = $classroom->customClassroomGroups()
                    ->whereIn('id', $collection->pluck('id'))
                    ->get();

                foreach ($groupsToUpdate as $group) {
                    $this->classroomGroupService->update($group, $collection->firstWhere('id', $group->id));
                }
            }
        });

        return $this->successResponse(message: 'Classroom groups are updated successfully.');
    }
}
