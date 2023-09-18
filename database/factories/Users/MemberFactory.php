<?php

namespace Database\Factories\Users;

use App\Models\School;
use App\Models\Users\Member;
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
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ];
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
