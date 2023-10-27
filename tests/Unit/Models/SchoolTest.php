<?php

namespace Tests\Unit\Models;

use App\Models\School;
use App\Models\Users\Member;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see School::teachers()
     */
    public function test_a_traditional_school_has_many_teachers(): void
    {
        $school = $this->fakeTraditionalSchool();

        $this->fakeNonAdminTeacher($school, 10);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->teachers());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->teachers()->count());

        foreach ($school->teachers as $teacher) {
            // Assert that the instructors are teachers
            $this->assertInstanceOf(Teacher::class, $teacher);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $teacher->school_id);
        }
    }

    /**
     * @see School::owner()
     */
    public function test_a_homeschool_has_one_owner(): void
    {
        $school = $this->fakeHomeschool();

        Member::factory()
            ->count(1)
            ->ofSchool($school)
            ->create();

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasOne::class, $school->owner());

        // Assert that the school has only one owner
        $this->assertEquals(1, $school->owner()->count());

        $this->assertInstanceOf(Member::class, $school->owner);
        $this->assertEquals($school->id, $school->owner->school_id);
    }

    /**
     * @see School::students()
     */
    public function test_a_traditional_school_has_many_students(): void
    {
        $school = $this->fakeTraditionalSchool();

        $this->fakeStudent($school, 10);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->students());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->students()->count());

        foreach ($school->students as $student) {
            // Assert that the instructors are students
            $this->assertInstanceOf(Student::class, $student);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $student->school_id);
        }
    }

    /**
     * @see School::scopeTraditionalSchools()
     */
    public function test_it_gets_traditional_schools(): void
    {
        $traditionalSchools = $this->fakeTraditionalSchool(10);

        $homeschools = $this->fakeHomeschool(10);

        $result = School::traditionalSchools()->get();

        // Assert the number of found schools is correct
        $this->assertCount(10, $result);

        // Assert all traditional schools are excluded
        foreach ($traditionalSchools as $traditionalSchool) {
            $this->assertTrue($result->contains($traditionalSchool));
        }

        // Assert all homeschools are included
        foreach ($homeschools as $homeschool) {
            $this->assertFalse($result->contains($homeschool));
        }
    }

    /**
     * @see School::scopeHomeschools()
     */
    public function test_it_gets_homeschools(): void
    {
        $traditionalSchools = $this->fakeTraditionalSchool(10);

        $homeschools = $this->fakeHomeschool(10);

        $result = School::homeschools()->get();

        // Assert the number of found schools is correct
        $this->assertCount(10, $result);

        // Assert all traditional schools are excluded
        foreach ($traditionalSchools as $traditionalSchool) {
            $this->assertFalse($result->contains($traditionalSchool));
        }

        // Assert all homeschools are included
        foreach ($homeschools as $homeschool) {
            $this->assertTrue($result->contains($homeschool));
        }
    }
}
