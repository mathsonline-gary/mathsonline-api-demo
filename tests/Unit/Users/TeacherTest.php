<?php

namespace Tests\Unit\Users;

use App\Models\Classroom;
use App\Models\School;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return void
     * @see Teacher::school()
     */
    public function test_a_teacher_belongs_to_a_school(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = School::factory()->create([
            'market_id' => 1,
            'type' => 'traditional school',
        ]);

        $teacher = Teacher::factory()->create([
            'school_id' => $school->id,
        ]);

        // Assert that the teacher has a relationship with the school
        $this->assertInstanceOf(BelongsTo::class, $teacher->school());

        // Assert that the teacher's school is an instance of School
        $this->assertInstanceOf(School::class, $teacher->school);

        // Assert that the teacher is associated with the correct school
        $this->assertEquals($school->id, $teacher->school->id);
    }

    /**
     * @return void
     * @see Teacher::classroomsAsOwner()
     */
    public function test_a_teacher_may_own_classrooms()
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = School::factory()
            ->traditionalSchool()
            ->create();

        $teacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        $classroom1 = Classroom::factory()
            ->ofSchool($school)
            ->ownedBy($teacher)
            ->create();

        $classroom2 = Classroom::factory()
            ->ofSchool($school)
            ->ownedBy($teacher)
            ->create();

        $classrooms = $teacher->classroomsAsOwner;

        // Assert that the classrooms collection is not empty
        $this->assertNotEmpty($classrooms);

        // Assert that the classrooms collection contains the created classrooms
        $this->assertTrue($classrooms->contains($classroom1));
        $this->assertTrue($classrooms->contains($classroom2));
    }

    /**
     * @return void
     * @see Teacher::classroomsAsOwner()
     */
    public function test_a_teacher_may_not_own_any_classroom()
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = School::factory()
            ->traditionalSchool()
            ->create();

        $teacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        $classrooms = $teacher->classroomsAsOwner;

        // Assert that the classrooms collection is empty
        $this->assertEmpty($classrooms);
    }

    /**
     * @return void
     * @see Teacher::classroomsAsSecondaryTeacher()
     */
    public function test_a_teacher_may_be_the_secondary_teacher_of_classrooms()
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = School::factory()
            ->traditionalSchool()
            ->create();

        $owner = Teacher::factory()
            ->ofSchool($school)
            ->create();

        $secondaryTeacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        $classroom1 = Classroom::factory()
            ->ofSchool($school)
            ->ownedBy($owner)
            ->create();

        $classroom2 = Classroom::factory()
            ->ofSchool($school)
            ->ownedBy($owner)
            ->create();

        $secondaryTeacher->classroomsAsSecondaryTeacher()->attach([$classroom1->id, $classroom2->id]);

        // Get secondary classrooms of $secondaryTeacher
        $classrooms1 = $secondaryTeacher->classroomsAsSecondaryTeacher;

        // Assert that the classrooms collection is not empty
        $this->assertNotEmpty($classrooms1);

        // Assert that the classrooms collection contains the created classrooms
        $this->assertTrue($classrooms1->contains($classroom1));
        $this->assertTrue($classrooms1->contains($classroom2));
    }

    /**
     * @return void
     * @see Teacher::classroomsAsSecondaryTeacher()
     */
    public function test_a_teacher_may_not_be_the_secondary_teacher_of_any_classroom()
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = School::factory()
            ->traditionalSchool()
            ->create();

        $owner = Teacher::factory()
            ->ofSchool($school)
            ->create();

        $nonSecondaryTeacher = Teacher::factory()
            ->ofSchool($school)
            ->create();

        $classroom1 = Classroom::factory()
            ->ofSchool($school)
            ->ownedBy($owner)
            ->create();

        $classroom2 = Classroom::factory()
            ->ofSchool($school)
            ->ownedBy($owner)
            ->create();

        // Get secondary classrooms of $nonSecondaryTeacher
        $classrooms = $nonSecondaryTeacher->classroomsAsSecondaryTeacher;

        // Assert that the classrooms collection is empty
        $this->assertEmpty($classrooms);

        // Assert that the classrooms collection does not contain the created classrooms
        $this->assertFalse($classrooms->contains($classroom1));
        $this->assertFalse($classrooms->contains($classroom2));
    }

    /**
     * @return void
     * @see Teacher::isAdmin()
     */
    public function test_a_teacher_is_admin()
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = School::factory()
            ->traditionalSchool()
            ->create();

        // Create a teacher admin
        $adminTeacher = Teacher::factory()
            ->ofSchool($school)
            ->admin()
            ->create();

        // Call the isAdmin method and assert that it returns true
        $this->assertTrue($adminTeacher->isAdmin());

        // Create a non-admin teacher
        $nonAdminTeacher = Teacher::factory()
            ->ofSchool($school)
            ->create([
                'is_admin' => false,
            ]);

        // Call the isAdmin method and assert that it returns false
        $this->assertFalse($nonAdminTeacher->isAdmin());
    }
}
