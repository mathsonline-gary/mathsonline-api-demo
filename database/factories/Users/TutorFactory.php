<?php

namespace Database\Factories\Users;

use App\Models\School;
use App\Models\Users\Tutor;
use App\Models\Users\TutorType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tutor>
 */
class TutorFactory extends Factory
{
    protected $model = Tutor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_id' => fake()->randomElement(TutorType::pluck('id')->all()),
            'email' => fake()->safeEmail(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        ];
    }

    /**
     * Indicate the tutor is a primary tutor.
     *
     * @return TutorFactory
     */
    public function primary(): TutorFactory
    {
        return $this->state(function () {
            return [
                'type_id' => TutorType::where(['name' => TutorType::PRIMARY_PARENT])->first()->id,
            ];
        });
    }

    /**
     * Indicate the tutor is a secondary tutor.
     *
     * @return TutorFactory
     */
    public function secondary(): TutorFactory
    {
        return $this->state(function () {
            return [
                'type_id' => TutorType::where(['name' => TutorType::SECONDARY_PARENT])->first()->id,
            ];
        });
    }

    /**
     * Indicate the tutor to belong to a given school.
     *
     * @param School $school
     * @return TutorFactory
     */
    public function ofSchool(School $school): TutorFactory
    {
        return $this->state(function () use ($school) {
            return [
                'school_id' => $school->id,
            ];
        });
    }
}
