<?php

namespace Tests\Traits;

use App\Models\Users\Admin;
use Illuminate\Database\Eloquent\Collection;

trait AdminTestHelpers
{
    /**
     * Create fake admin(s).
     *
     * @param int $count
     * @param array $attributes
     *
     * @return Admin|Collection<Admin>
     */
    public function fakeAdmin(int $count = 1, array $attributes = []): Admin|Collection
    {
        $admin = Admin::factory()
            ->count($count)
            ->create($attributes);

        return $count === 1 ? $admin->first() : $admin;
    }

    /**
     * Set the currently logged-in admin for the application.
     *
     * @param Admin $admin
     * @return void
     */
    public function actingAsAdmin(Admin $admin): void
    {
        $this->actingAs($admin->asUser());
    }
}
