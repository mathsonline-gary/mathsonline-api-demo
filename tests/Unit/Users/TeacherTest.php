<?php

namespace Tests\Unit\Users;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_teacher_belongs_to_a_school(): void
    {
        $school = School::factory()->create([
            'market_id' => 1,
            'type' => 'traditional school',
        ]);

        $teacher = Teacher::factory()->create([
            'market_id' => $school->market_id,
            'school_id' => $school->id,
        ]);

        // Assert that the teacher has a relationship with the school
        $this->assertInstanceOf(BelongsTo::class, $teacher->school());

        // Assert that the teacher's school is an instance of School
        $this->assertInstanceOf(School::class, $teacher->school);

        // Assert that the teacher is associated with the correct school
        $this->assertEquals($school->id, $teacher->school->id);
    }
}
