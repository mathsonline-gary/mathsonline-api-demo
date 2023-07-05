<?php

namespace Tests\Unit\Models\Users;

use App\Models\Classroom;
use App\Models\School;
use App\Models\Users\Teacher;
use Database\Seeders\MarketSeeder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use function Symfony\Component\String\s;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @see Teacher::school()
     */
    public function test_a_teacher_belongs_to_a_school(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        // Assert that the teacher has a relationship with the school
        $this->assertInstanceOf(BelongsTo::class, $teacher->school());

        // Assert that the teacher's school is an instance of School
        $this->assertInstanceOf(School::class, $teacher->school);

        // Assert that the teacher is associated with the correct school
        $this->assertEquals($school->id, $teacher->school->id);
    }

    /**
     * @see Teacher::ownedClassrooms()
     */
    public function test_a_teacher_may_own_classrooms(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($teacher);
        $classroom2 = $this->fakeClassroom($teacher);

        $classrooms = $teacher->classroomsAsOwner;

        // Assert that the classrooms collection is not empty
        $this->assertNotEmpty($classrooms);

        // Assert that the classrooms collection contains the created classrooms
        $this->assertTrue($classrooms->contains($classroom1));
        $this->assertTrue($classrooms->contains($classroom2));
    }

    /**
     * @see Teacher::ownedClassrooms()
     */
    public function test_a_teacher_may_not_own_classroom(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        $classrooms = $teacher->classroomsAsOwner;

        // Assert that the classrooms collection is empty
        $this->assertEmpty($classrooms);
    }

    /**
     * @see Teacher::isClassroomOwner()
     */
    public function test_it_knows_if_a_teacher_is_classroom_owner(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        // Assert that the teacher does not own a classroom
        $this->assertFalse($teacher->isClassroomOwner());

        $this->fakeClassroom($teacher);

        // Assert that the teacher owns a classroom
        $this->assertTrue($teacher->isClassroomOwner());
    }

    /**
     * @see Teacher::secondaryClassrooms()
     */
    public function test_a_teacher_may_be_the_secondary_teacher_of_classrooms(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $secondaryTeacher = $this->fakeNonAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($owner);

        $classroom2 = $this->fakeClassroom($owner);

        $secondaryTeacher->secondaryClassrooms()->attach([$classroom1->id, $classroom2->id]);

        // Get secondary classrooms of $secondaryTeacher
        $classrooms1 = $secondaryTeacher->classroomsAsSecondaryTeacher;

        // Assert that the classrooms collection is not empty
        $this->assertNotEmpty($classrooms1);

        // Assert that the classrooms collection contains the created classrooms
        $this->assertTrue($classrooms1->contains($classroom1));
        $this->assertTrue($classrooms1->contains($classroom2));
    }

    /**
     * @see Teacher::secondaryClassrooms()
     */
    public function test_a_teacher_may_not_be_the_secondary_teacher_of_classroom(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($owner);

        $classroom2 = $this->fakeClassroom($owner);

        // Get secondary classrooms of $nonSecondaryTeacher
        $classrooms = $teacher->classroomsAsSecondaryTeacher;

        // Assert that the classrooms collection is empty
        $this->assertEmpty($classrooms);

        // Assert that the classrooms collection does not contain the created classrooms
        $this->assertFalse($classrooms->contains($classroom1));
        $this->assertFalse($classrooms->contains($classroom2));
    }

    /**
     * @see Teacher::isSecondaryTeacher()
     */
    public function test_if_a_teacher_is_secondary_teacher(): void
    {
        $this->seed([
            MarketSeeder::class
        ]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        // Assert that $teacher is not a secondary teacher
        $this->assertFalse($teacher->isSecondaryTeacher());

        $this->attachSecondaryTeachersToClassroom($classroom, [$teacher->id]);

        // Assert that $teacher is a secondary teacher
        $this->assertTrue($teacher->isSecondaryTeacher());
    }

    /**
     * @see Teacher::isAdmin()
     */
    public function test_it_knows_whether_a_teacher_is_admin(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        // Create a teacher admin
        $adminTeacher = $this->fakeAdminTeacher($school);

        // Call the isAdmin method and assert that it returns true
        $this->assertTrue($adminTeacher->isAdmin());

        // Create a non-admin teacher
        $nonAdminTeacher = $this->fakeNonAdminTeacher($school);

        // Call the isAdmin method and assert that it returns false
        $this->assertFalse($nonAdminTeacher->isAdmin());
    }

    /**
     * @see Teacher::activities()
     */
    public function test_a_teacher_has_many_activities_logged(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);
        $this->fakeActivity($teacher, 10);

        $this->assertInstanceOf(MorphMany::class, $teacher->activities());
        $this->assertEquals(10, $teacher->activities()->count());
    }

    /**
     * @see Teacher::asTeacher()
     */
    public function test_a_teacher_is_a_teacher(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->assertInstanceOf(Teacher::class, $teacher->asTeacher());
        $this->assertEquals($teacher->id, $teacher->asTeacher()->id);
    }

    /**
     * @see Teacher::asStudent()
     */
    public function test_a_teacher_is_not_a_student(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->assertNull($teacher->asStudent());
    }

    /**
     * @see Teacher::asTutor()
     */
    public function test_a_teacher_is_not_a_tutor(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->assertNull($teacher->asTutor());
    }

    /**
     * @see Teacher::asAdmin()
     */
    public function test_a_teacher_is_not_an_admin(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->assertNull($teacher->asAdmin());
    }

    /**
     * @see Teacher::asDeveloper()
     */
    public function test_a_teacher_is_not_a_developer(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();
        $teacher = $this->fakeAdminTeacher($school);

        $this->assertNull($teacher->asDeveloper());
    }

    /**
     * @see Teacher::isSecondaryTeacherOfClassroom()
     */
    public function test_a_teacher_may_be_the_secondary_teacher_of_a_classroom(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        // Assert that the teacher is not the secondary teacher of the classroom
        $this->assertFalse($teacher->isSecondaryTeacherOfClassroom($classroom));

        $teacher->secondaryClassrooms()->attach($classroom->id);

        // Assert that the teacher is the secondary teacher of the classroom
        $this->assertTrue($teacher->isSecondaryTeacherOfClassroom($classroom));
    }

    /**
     * @see Teacher::isOwnerOfClassroom()
     */
    public function test_a_teacher_may_be_the_owner_of_a_classroom(): void
    {
        $this->seed([MarketSeeder::class]);

        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        // Assert that $teacher is not the owner of the classroom
        $this->assertFalse($teacher->isOwnerOfClassroom($classroom));

        // Assert that $owner is the owner of the classroom
        $this->assertTrue($owner->isOwnerOfClassroom($classroom));
    }
}
