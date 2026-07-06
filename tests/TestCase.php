<?php

namespace Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Create a super admin (bypasses all permission checks). */
    protected function superAdmin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    /** Create a user whose role grants the given feature permissions. */
    protected function userWithPermissions(array $permissions, ?string $roleName = null): User
    {
        $role = Role::create(['name' => $roleName ?? 'Role '.fake()->unique()->word()]);

        $ids = collect($permissions)->map(
            fn ($name) => Permission::firstOrCreate(['name' => $name], ['label' => ucfirst($name)])->id
        );
        $role->permissions()->sync($ids);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
