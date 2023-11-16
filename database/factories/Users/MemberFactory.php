<?php

namespace Database\Factories\Users;

use App\Models\School;
use App\Models\Users\Member;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->member()->create()->id,
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
        ];
    }

    /**
     * Configure the model factory: synchronize attributes with the associated user.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Member $member) {
            $user = $member->asUser();

            $user->login = $member->email;
            $user->email = $member->email;
            $user->email_verified_at = now();

            $user->save();
        })->afterCreating(function (Member $member) {
            $user = $member->asUser();

            $user->login = $member->email;
            $user->email = $member->email;
            $user->email_verified_at = now();

            $user->save();
        });
    }

    /**
     * Indicate the member to belong to a given school.
     *
     * @param School $school
     * @return MemberFactory
     */
    public function ofSchool(School $school): MemberFactory
    {
        return $this->state(function () use ($school) {
            return [
                'school_id' => $school->id,
            ];
        });
    }
}
