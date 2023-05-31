<?php

namespace Tests\Unit\Users;

use App\Models\School;
use App\Models\Users\Tutor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_tutor_belongs_to_a_school(): void
    {
        $school = School::factory()->create([
            'market_id' => 1,
            'type' => 'homeschool',
        ]);

        $tutor = Tutor::factory()->create([
            'market_id' => $school->market_id,
            'school_id' => $school->id,
        ]);

        // Assert that the teacher has a relationship with the school
        $this->assertInstanceOf(BelongsTo::class, $tutor->school());

        // Assert that the tutor's school is an instance of School
        $this->assertInstanceOf(School::class, $tutor->school);

        // Assert that the tutor is associated with the correct school
        $this->assertEquals($tutor->school->id, $school->id);
    }
}
