<?php

namespace Tests\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Collection;

trait SchoolTestHelpers
{
    /**
     * Create fake school(s).
     *
     * @param int $count
     * @param array $attributes
     * @return Collection|School
     */
    public function fakeSchool(int $count = 1, array $attributes = []): Collection|School
    {
        $schools = School::factory()
            ->count($count)
            ->create($attributes);

        return $count === 1 ? $schools->first() : $schools;
    }

    /**
     * Create fake traditional school(s).
     *
     * @param int $count
     * @param array $attributes
     * @return Collection<School>|School
     */
    public function fakeTraditionalSchool(int $count = 1, array $attributes = []): Collection|School
    {
        $schools = School::factory()
            ->count($count)
            ->traditionalSchool()
            ->create($attributes);

        return $count === 1 ? $schools->first() : $schools;
    }

    /**
     * Create fake homeschool(s).
     *
     * @param int $count
     * @param array $attributes
     * @return Collection<School>|School
     */
    public function fakeHomeschool(int $count = 1, array $attributes = []): Collection|School
    {
        $schools = School::factory()
            ->count($count)
            ->homeschool()
            ->create($attributes);

        return $count === 1 ? $schools->first() : $schools;
    }

    /**
     * Assert that the given school has the given expected attributes.
     *
     * @param array $expected
     * @param School $school
     * @return void
     */
    public function assertSchoolAttributes(array $expected, School $school): void
    {
        foreach ($expected as $attribute => $value) {
            switch ($attribute) {
                case 'type':
                    is_int($value)
                        ? $this->assertEquals(
                        $value,
                        $school->type->value,
                        'The school attribute type does not match the expected value.'
                    )
                        : $this->assertEquals(
                        $value,
                        $school->type,
                        'The school attribute type does not match the expected value.'
                    );
                    break;

                default:
                    $this->assertEquals(
                        $value,
                        $school->{$attribute},
                        "The school attribute '$attribute' does not match the expected value."
                    );
                    break;
            }
        }
    }
}
