<?php

namespace Tests\Unit\Users;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_belongs_to_a_school(): void
    {
        $school = School::factory()->create([
            'market_id' => 1,
            'type' => 'traditional school',
        ]);

        $student = Teacher::factory()->create([
            'market_id' => $school->market_id,
            'school_id' => $school->id,
        ]);

        // Assert that the student has a relationship with the school
        $this->assertInstanceOf(BelongsTo::class, $student->school());

        // Assert that the student's school is an instance of School
        $this->assertInstanceOf(School::class, $student->school);

        // Assert that the student is associated with the correct school
        $this->assertEquals($school->id, $student->school->id);
    }
}
