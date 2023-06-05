<?php

namespace Tests\Feature\TeacherApis\Teachers;

use Tests\TestCase;

class TeacherUpdatingTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
