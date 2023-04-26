<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\SchoolService;
use App\Services\TutorService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected SchoolService $schoolService,
        protected TutorService  $tutorService,
    )
    {
    }

    /**
     * @param RegisterRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(RegisterRequest $request): Response
    {
        $validated = $request->safe()->only([
            'market_id',
            'first_name',
            'last_name',
            'email',
            'password',
            'phone',
            'address_line_1',
            'address_line_2',
            'address_city',
            'address_state',
            'address_postal_code',
            'address_country'
        ]);

        $tutor = null;

        try {
            DB::transaction(function () use ($validated, $request, &$tutor) {
                $school = $this->schoolService->create([
                    ...$validated,
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'] . "'s Homeschool",
                    'type' => 'homeschool',
                ]);

                $tutor = $this->tutorService->create([
                    ...$validated,
                    'type_id' => 1,
                    'school_id' => $school->id,
                ]);

                event(new Registered($tutor));
            });
        } catch (Throwable $exception) {
            Log::error('Failed to register: ', [
                ...Arr::only($validated, [
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                ]),
                'exception' => $exception->getMessage(),
            ]);

            // TODO: Mail to account manager about the failure.

            throw $exception;
        }

        return response(['token' => $tutor->createToken('api_token')->plainTextToken], 201);
    }
}
