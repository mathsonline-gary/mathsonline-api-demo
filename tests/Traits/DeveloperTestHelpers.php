<?php

namespace Tests\Traits;

use App\Models\Users\Developer;
use Illuminate\Database\Eloquent\Collection;

trait DeveloperTestHelpers
{
    /**
     * Create fake developer(s).
     *
     * @param int $count
     * @param array $attributes
     *
     * @return Developer|Collection<Developer>
     */
    public function fakeDeveloper(int $count = 1, array $attributes = []): Developer|Collection
    {
        $developers = Developer::factory()
            ->count($count)
            ->create($attributes);

        return $count === 1 ? $developers->first() : $developers;
    }

    /**
     * Set the currently logged-in student for the application.
     *
     * @param Developer $developer
     *
     * @return void
     */
    public function actingAsDeveloper(Developer $developer): void
    {
        $this->actingAs($developer->asUser());
    }
}
