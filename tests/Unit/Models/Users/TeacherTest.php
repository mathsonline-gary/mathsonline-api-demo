<?php

namespace Tests\Unit\Models\Users;

use App\Models\School;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    /**
     * @see Teacher::school()
     */
    public function test_a_teacher_belongs_to_a_school(): void
    {
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
        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($teacher);
        $classroom2 = $this->fakeClassroom($teacher);

        $classrooms = $teacher->ownedClassrooms;

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
        $school = $this->fakeTraditionalSchool();

        $teacher = $this->fakeAdminTeacher($school);

        $classrooms = $teacher->ownedClassrooms;

        // Assert that the classrooms collection is empty
        $this->assertEmpty($classrooms);
    }

    /**
     * @see Teacher::isClassroomOwner()
     */
    public function test_it_knows_if_a_teacher_is_classroom_owner(): void
    {
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
        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $secondaryTeacher = $this->fakeNonAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($owner);

        $classroom2 = $this->fakeClassroom($owner);

        $secondaryTeacher->secondaryClassrooms()->attach([$classroom1->id, $classroom2->id]);

        // Get secondary classrooms of $secondaryTeacher
        $classrooms1 = $secondaryTeacher->secondaryClassrooms;

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
        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);

        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($owner);

        $classroom2 = $this->fakeClassroom($owner);

        // Get secondary classrooms of $nonSecondaryTeacher
        $classrooms = $teacher->secondaryClassrooms;

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
     * @see Teacher::isSecondaryTeacherOfClassroom()
     */
    public function test_a_teacher_may_be_the_secondary_teacher_of_a_classroom(): void
    {
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
        $school = $this->fakeTraditionalSchool();

        $owner = $this->fakeAdminTeacher($school);
        $teacher = $this->fakeNonAdminTeacher($school);

        $classroom = $this->fakeClassroom($owner);

        // Assert that $teacher is not the owner of the classroom
        $this->assertFalse($teacher->isOwnerOfClassroom($classroom));

        // Assert that $owner is the owner of the classroom
        $this->assertTrue($owner->isOwnerOfClassroom($classroom));
    }

    /**
     * @see Teacher::getOwnedAndSecondaryClassrooms()
     */
    public function test_a_teacher_can_get_owned_and_secondary_classrooms(): void
    {
        $school = $this->fakeTraditionalSchool();

        $teacher1 = $this->fakeAdminTeacher($school);
        $teacher2 = $this->fakeNonAdminTeacher($school);

        $classroom1 = $this->fakeClassroom($teacher1);
        $classroom2 = $this->fakeClassroom($teacher1);
        $classroom3 = $this->fakeClassroom($teacher1);

        $classroom4 = $this->fakeClassroom($teacher2);
        $classroom5 = $this->fakeClassroom($teacher2);

        $classroom6 = $this->fakeClassroom($teacher1);

        $this->attachSecondaryTeachersToClassroom($classroom1, [$teacher2->id]);
        $this->attachSecondaryTeachersToClassroom($classroom2, [$teacher2->id]);
        $this->attachSecondaryTeachersToClassroom($classroom3, [$teacher2->id]);

        $classrooms = $teacher2->getOwnedAndSecondaryClassrooms();

        $this->assertInstanceOf(Collection::class, $classrooms);
        $this->assertEquals(5, $classrooms->count());
        $this->assertTrue($classrooms->contains($classroom1));
        $this->assertTrue($classrooms->contains($classroom2));
        $this->assertTrue($classrooms->contains($classroom3));
        $this->assertTrue($classrooms->contains($classroom4));
        $this->assertTrue($classrooms->contains($classroom5));
        $this->assertFalse($classrooms->contains($classroom6));
    }

    /**
     * @see Teacher::canManageStudent()
     */
    public function test_it_indicates_that_the_admin_teacher_can_manage_the_student()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);

        $student1 = $this->fakeStudent($school1);
        $student2 = $this->fakeStudent($school2);

        $this->assertTrue($adminTeacher->canManageStudent($student1));
        $this->assertFalse($adminTeacher->canManageStudent($student2));
    }

    public function test_it_indicates_that_the_non_admin_teacher_can_manage_the_student()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school1);

        $student1 = $this->fakeStudent($school1);
        $student2 = $this->fakeStudent($school1);
        $student3 = $this->fakeStudent($school1);
        $student4 = $this->fakeStudent($school2);

        $classroom1 = $this->fakeClassroom($nonAdminTeacher1);
        $classroom2 = $this->fakeClassroom($nonAdminTeacher2);
        $classroom3 = $this->fakeClassroom($nonAdminTeacher2);

        $this->attachSecondaryTeachersToClassroom($classroom2, [$nonAdminTeacher1->id]);

        $this->attachStudentsToClassroomGroup($classroom1->defaultClassroomGroup, [$student1->id]);
        $this->attachStudentsToClassroomGroup($classroom2->defaultClassroomGroup, [$student2->id]);
        $this->attachStudentsToClassroomGroup($classroom3->defaultClassroomGroup, [$student3->id]);

        $this->assertTrue($nonAdminTeacher1->canManageStudent($student1));
        $this->assertTrue($nonAdminTeacher1->canManageStudent($student2));
        $this->assertFalse($nonAdminTeacher1->canManageStudent($student3));
        $this->assertFalse($nonAdminTeacher1->canManageStudent($student4));
    }

    /**
     * @see Teacher::canManageClassroom()
     */
    public function test_it_indicates_that_the_admin_teacher_can_manage_the_classroom()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $adminTeacher = $this->fakeAdminTeacher($school1);

        $classroom1 = $this->fakeClassroom($adminTeacher);
        $classroom2 = $this->fakeClassroom($adminTeacher);
        $classroom3 = $this->fakeClassroom($adminTeacher);

        $this->assertTrue($adminTeacher->canManageClassroom($classroom1));
        $this->assertTrue($adminTeacher->canManageClassroom($classroom2));
        $this->assertTrue($adminTeacher->canManageClassroom($classroom3));

        $classroom4 = $this->fakeClassroom($this->fakeAdminTeacher($school2));

        $this->assertFalse($adminTeacher->canManageClassroom($classroom4));
    }

    /**
     * @see Teacher::canManageClassroom()
     */
    public function test_it_indicates_that_the_non_admin_teacher_can_manage_the_classroom()
    {
        $school1 = $this->fakeTraditionalSchool();
        $school2 = $this->fakeTraditionalSchool();

        $nonAdminTeacher1 = $this->fakeNonAdminTeacher($school1);
        $nonAdminTeacher2 = $this->fakeNonAdminTeacher($school1);

        $classroom1 = $this->fakeClassroom($nonAdminTeacher1);
        $classroom2 = $this->fakeClassroom($nonAdminTeacher2);
        $classroom3 = $this->fakeClassroom($nonAdminTeacher2);

        $this->attachSecondaryTeachersToClassroom($classroom2, [$nonAdminTeacher1->id]);

        $this->assertTrue($nonAdminTeacher1->canManageClassroom($classroom1));
        $this->assertTrue($nonAdminTeacher1->canManageClassroom($classroom2));
        $this->assertFalse($nonAdminTeacher1->canManageClassroom($classroom3));

        $classroom4 = $this->fakeClassroom($this->fakeAdminTeacher($school2));

        $this->assertFalse($nonAdminTeacher1->canManageClassroom($classroom4));
    }

}
