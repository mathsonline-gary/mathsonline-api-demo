<?php

namespace Tests\Unit\Models;

use App\Models\School;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use App\Models\Users\Tutor;
use Database\Seeders\MarketSeeder;
use Database\Seeders\TutorTypeSeeder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     *
     * @see School::teachers()
     */
    public function test_a_traditional_school_has_many_teachers(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $this->fakeNonAdminTeacher($school, 10);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->teachers());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->teachers()->count());

        foreach ($school->teachers as $teacher) {
            // Assert that the instructors are tutors
            $this->assertInstanceOf(Teacher::class, $teacher);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $teacher->school_id);
        }
    }

    /**
     * @return void
     *
     * @see School::tutors()
     */
    public function test_a_homeschool_has_many_tutors(): void
    {
        $this->seed([
            MarketSeeder::class,
            TutorTypeSeeder::class,
        ]);

        $school = $this->fakeHomeSchool();

        Tutor::factory()
            ->count(10)
            ->ofSchool($school)
            ->create();

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->tutors());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->tutors()->count());

        foreach ($school->tutors as $tutor) {
            // Assert that the instructors are tutors
            $this->assertInstanceOf(Tutor::class, $tutor);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $tutor->school_id);
        }
    }

    /**
     * @return void
     *
     * @see School::students()
     */
    public function test_a_traditional_school_has_many_students(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $this->fakeStudent($school, 10);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->students());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->students()->count());

        foreach ($school->students as $student) {
            // Assert that the instructors are tutors
            $this->assertInstanceOf(Student::class, $student);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $student->school_id);
        }
    }

    /**
     * @return void
     *
     * @see School::scopeTraditionalSchools()
     *
     * @test
     */
    public function it_gets_traditional_schools(): void
    {
        $this->seed([MarketSeeder::class]);

        $traditionalSchools = $this->fakeTraditionalSchool(10);

        $homeschools = $this->fakeHomeSchool(10);

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
     * @return void
     *
     * @see School::scopeHomeschools()
     *
     * @test
     */
    public function it_gets_homeschools(): void
    {
        $this->seed([MarketSeeder::class]);

        $traditionalSchools = $this->fakeTraditionalSchool(10);

        $homeschools = $this->fakeHomeSchool(10);

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
