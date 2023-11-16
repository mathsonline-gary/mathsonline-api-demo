<?php

namespace Tests\Unit\Models;

use App\Enums\SchoolType;
use App\Enums\SubscriptionStatus;
use App\Models\Campaign;
use App\Models\Membership;
use App\Models\Product;
use App\Models\School;
use App\Models\Subscription;
use App\Models\Users\Member;
use App\Models\Users\Student;
use App\Models\Users\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class SchoolTest extends TestCase
{
    /**
     * @see School::teachers()
     */
    public function test_a_traditional_school_has_many_teachers(): void
    {
        $school = $this->fakeTraditionalSchool();

        $this->fakeNonAdminTeacher($school, 10);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->teachers());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->teachers()->count());

        foreach ($school->teachers as $teacher) {
            // Assert that the instructors are teachers
            $this->assertInstanceOf(Teacher::class, $teacher);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $teacher->school_id);
        }
    }

    /**
     * @see School::owner()
     */
    public function test_a_homeschool_has_one_owner(): void
    {
        $school = $this->fakeHomeschool();

        Member::factory()
            ->count(1)
            ->ofSchool($school)
            ->create();

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasOne::class, $school->owner());

        // Assert that the school has only one owner
        $this->assertEquals(1, $school->owner()->count());

        $this->assertInstanceOf(Member::class, $school->owner);
        $this->assertEquals($school->id, $school->owner->school_id);
    }

    /**
     * @see School::students()
     */
    public function test_a_traditional_school_has_many_students(): void
    {
        $school = $this->fakeTraditionalSchool();

        $this->fakeStudent($school, 10);

        // Assert that the school has a relationship with the instructors
        $this->assertInstanceOf(HasMany::class, $school->students());

        // Assert that the school has the correct number of instructors
        $this->assertEquals(10, $school->students()->count());

        foreach ($school->students as $student) {
            // Assert that the instructors are students
            $this->assertInstanceOf(Student::class, $student);

            // Assert that the instructors are associated with the correct school
            $this->assertEquals($school->id, $student->school_id);
        }
    }

    /**
     * @see School::scopeTraditionalSchools()
     */
    public function test_it_gets_traditional_schools(): void
    {
        $traditionalSchools = $this->fakeTraditionalSchool(10);

        $homeschools = $this->fakeHomeschool(10);

        $result = School::traditionalSchools()->get();

        // Assert the number of found schools is correct
        $this->assertCount(10, $result);

        // Assert all traditional schools are excluded
        foreach ($traditionalSchools as $traditionalSchool) {
            $this->assertTrue($result->contains($traditionalSchool));
        }

        // Assert all homeschools are included
        foreach ($homeschools as $homeschool) {
            $this->assertFalse($result->contains($homeschool));
        }
    }

    /**
     * @see School::scopeHomeschools()
     */
    public function test_it_gets_homeschools(): void
    {
        $traditionalSchools = $this->fakeTraditionalSchool(10);

        $homeschools = $this->fakeHomeschool(10);

        $result = School::homeschools()->get();

        // Assert the number of found schools is correct
        $this->assertCount(10, $result);

        // Assert all traditional schools are excluded
        foreach ($traditionalSchools as $traditionalSchool) {
            $this->assertFalse($result->contains($traditionalSchool));
        }

        // Assert all homeschools are included
        foreach ($homeschools as $homeschool) {
            $this->assertTrue($result->contains($homeschool));
        }
    }

    /**
     * @see School::subscriptions()
     */
    public function test_it_has_many_subscriptions(): void
    {
        $homeschool = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        $subscriptions = $this->fakeSubscription($homeschool, count: 10);

        // Assert that the school has a relationship with the subscriptions.
        $this->assertInstanceOf(HasMany::class, $homeschool->subscriptions());

        // Assert that the school has the correct number of subscriptions.
        $this->assertEquals(10, $homeschool->subscriptions()->count());

        foreach ($homeschool->subscriptions as $subscription) {
            // Assert that the subscriptions are subscriptions.
            $this->assertInstanceOf(Subscription::class, $subscription);

            // Assert that the subscriptions are associated with the correct school.
            $this->assertTrue($subscriptions->contains($subscription));
        }
    }

    /**
     * @see School::hasActiveSubscription()
     */
    public function test_it_indicates_whether_it_has_active_subscription(): void
    {
        $homeschool = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        $this->fakeSubscription($homeschool, status: SubscriptionStatus::CANCELED);

        $this->assertFalse($homeschool->hasActiveSubscription());

        $this->fakeSubscription($homeschool);

        $this->assertTrue($homeschool->hasActiveSubscription());
    }

    /**
     * @see School::canSubscribeToMembership()
     */
    public function test_it_returns_true_it_can_subscribe_to_a_membership(): void
    {
        $school = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        // Create an expired subscription for the school.
        $this->fakeSubscription($school, status: SubscriptionStatus::CANCELED);

        $product = Product::factory()->create([
            'school_type' => $school->type,
            'market_id' => $school->market_id,
        ]);

        $campaign = Campaign::factory()->active()->create();

        $membership = Membership::factory()->create([
            'product_id' => $product->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->assertTrue($school->canSubscribeToMembership($membership));
    }

    /**
     * @see School::canSubscribeToMembership()
     */
    public function test_it_returns_false_if_it_already_has_an_active_subscription(): void
    {
        $school = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        // Create an active subscription for the school.
        $this->fakeSubscription($school);

        $membership = Membership::whereHas('product', fn(Builder $query) => $query->where('market_id', $school->market_id))
            ->first();

        $this->assertFalse($school->canSubscribeToMembership($membership));
    }

    /**
     * @see School::canSubscribeToMembership()
     */
    public function test_it_returns_false_if_the_membership_is_inactive(): void
    {
        $school = $this->fakeHomeschool();

        $product = Product::factory()->create([
            'school_type' => $school->type,
            'market_id' => $school->market_id,
        ]);

        $campaign = Campaign::factory()->expired()->create();

        $membership = Membership::factory()->create([
            'product_id' => $product->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->assertFalse($school->canSubscribeToMembership($membership));
    }

    /**
     * @see School::canSubscribeToMembership()
     */
    public function test_it_returns_false_if_the_membership_is_in_another_market(): void
    {
        $school = $this->fakeHomeschool(attributes: ['market_id' => 1]);

        $product = Product::factory()->create([
            'school_type' => $school->type,
            'market_id' => 2,
        ]);

        $campaign = Campaign::factory()->active()->create();

        $membership = Membership::factory()->create([
            'product_id' => $product->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->assertFalse($school->canSubscribeToMembership($membership));
    }

    /**
     * @see School::canSubscribeToMembership()
     */
    public function test_it_returns_false_if_the_types_does_not_match(): void
    {
        $school = $this->fakeHomeschool();

        $product = Product::factory()->create([
            'school_type' => SchoolType::TRADITIONAL_SCHOOL,
            'market_id' => $school->market_id,
        ]);

        $campaign = Campaign::factory()->active()->create();

        $membership = Membership::factory()->create([
            'product_id' => $product->id,
            'campaign_id' => $campaign->id,
        ]);

        $this->assertFalse($school->canSubscribeToMembership($membership));
    }
}
