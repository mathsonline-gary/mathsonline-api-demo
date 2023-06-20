<?php

namespace Tests\Unit\Models\Users;

use App\Models\School;
use App\Models\Users\Tutor;
use Database\Seeders\MarketSeeder;
use Database\Seeders\TutorTypeSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tutor_belongs_to_a_school(): void
    {
        $this->seed([
            MarketSeeder::class,
            TutorTypeSeeder::class,
        ]);

        $school = School::factory()
            ->homeschool()
            ->create();

        $primaryTutor = Tutor::factory()
            ->ofSchool($school)
            ->primary()
            ->create();

        $secondaryTutor = Tutor::factory()
            ->ofSchool($school)
            ->secondary()
            ->create();

        // Assert that the teacher has a relationship with the school
        $this->assertInstanceOf(BelongsTo::class, $primaryTutor->school());
        $this->assertInstanceOf(BelongsTo::class, $secondaryTutor->school());

        // Assert that the tutor's school is an instance of School
        $this->assertInstanceOf(School::class, $primaryTutor->school);
        $this->assertInstanceOf(School::class, $secondaryTutor->school);

        // Assert that the tutor is associated with the correct school
        $this->assertEquals($primaryTutor->school->id, $school->id);
        $this->assertEquals($secondaryTutor->school->id, $school->id);
    }
}
