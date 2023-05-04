<?php

namespace Tests\Unit;

use App\Models\School;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_traditional_school_has_many_teachers_as_instructors(): void
    {
        $school = School::factory()->create([
            'market_id' => 1,
            'type' => 'traditional school',
        ]);

        Teacher::factory()->count(10)->create([
            'market_id' => $school->market_id,
            'school_id' => $school->id,
        ]);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->instructors());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->instructors()->count());

        foreach ($school->instructors as $instructor) {
            // Assert that the instructors are tutors
            $this->assertInstanceOf(Teacher::class, $instructor);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $instructor->school_id);
        }

    }

    public function test_a_homeschool_has_many_tutors_as_instructors(): void
    {
        $school = School::factory()->create([
            'market_id' => 1,
            'type' => 'homeschool',
        ]);

        Tutor::factory()->count(10)->create([
            'market_id' => $school->market_id,
            'school_id' => $school->id,
        ]);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->instructors());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->instructors()->count());

        foreach ($school->instructors as $instructor) {
            // Assert that the instructors are tutors
            $this->assertInstanceOf(Tutor::class, $instructor);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $instructor->school_id);
        }
    }
}
