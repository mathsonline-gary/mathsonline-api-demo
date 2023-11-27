<?php

namespace Tests\Feature\Subscriptions;

use App\Enums\SubscriptionStatus;
use Tests\TestCase;

class IndexSubscriptionTest extends TestCase
{
    protected string $routeName = 'api.v1.subscriptions.index';
    
    public function test_a_guest_is_unauthenticated_to_get_the_subscription_list(): void
    {
        $this->assertGuest();

        $response = $this->getJson(route($this->routeName));

        $response->assertUnauthorized();
    }

    public function test_a_student_is_unauthorized_to_get_the_subscription_list(): void
    {
        $student = $this->fakeStudent();

        $this->actingAsStudent($student);

        $response = $this->getJson(route($this->routeName));

        $response->assertForbidden();
    }

    public function test_a_developer_is_unauthorized_to_get_the_subscription_list(): void
    {
        $developer = $this->fakeDeveloper();

        $this->actingAsDeveloper($developer);

        $response = $this->getJson(route($this->routeName));

        $response->assertForbidden();
    }

    public function test_a_non_admin_teacher_is_unauthorized_to_get_the_subscription_list(): void
    {
        $teacher = $this->fakeNonAdminTeacher();

        $this->actingAsTeacher($teacher);

        $response = $this->getJson(route($this->routeName));

        $response->assertForbidden();
    }

    public function test_an_admin_is_unauthorized_to_get_the_subscription_list(): void
    {
        $admin = $this->fakeAdmin();

        $this->actingAsAdmin($admin);

        $response = $this->getJson(route($this->routeName));

        $response->assertForbidden();
    }

    public function test_a_member_can_get_their_subscription_list(): void
    {
        $member = $this->fakeMember();

        $activeSubscriptions = $this->fakeSubscription($member->school);
        $canceledSubscriptions = $this->fakeSubscription($member->school, SubscriptionStatus::CANCELED, null, 3);

        $this->actingAsMember($member);

        $response = $this->getJson(route($this->routeName));

        $response->assertOk()
            ->assertJsonSuccessful();

        // Assert that it only returns the subscriptions of the member.
        $response->assertJsonCount(4, 'data');
        $response->assertJsonFragment(['id' => $activeSubscriptions->id]);

        foreach ($canceledSubscriptions as $canceledSubscription) {
            $response->assertJsonFragment(['id' => $canceledSubscription->id]);
        }
    }

    public function test_an_admin_teacher_can_get_the_subscription_list_of_their_school(): void
    {
        $school = $this->fakeSchool(1, ['market_id' => 1]);
        $adminTeacher = $this->fakeAdminTeacher($school);

        $activeSubscriptions = $this->fakeSubscription($adminTeacher->school);
        $canceledSubscriptions = $this->fakeSubscription($adminTeacher->school, SubscriptionStatus::CANCELED, null, 3);

        $this->actingAsMember($adminTeacher);

        $response = $this->getJson(route($this->routeName));

        $response->assertOk()
            ->assertJsonSuccessful();

        // Assert that it only returns the subscriptions of the member.
        $response->assertJsonCount(4, 'data');
        $response->assertJsonFragment(['id' => $activeSubscriptions->id]);

        foreach ($canceledSubscriptions as $canceledSubscription) {
            $response->assertJsonFragment(['id' => $canceledSubscription->id]);
        }
    }
}
