<?php

namespace Tests\Feature\Teachers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TeacherIndexingTest extends TestCase
{
    public function test_teachers_with_administrator_access_can_get_the_list_of_teachers_in_the_school(): void
    {
    }

    public function test_teachers_without_administrator_access_are_unauthorised_to_get_the_list_of_teachers_in_the_school(): void
    {
    }
}
