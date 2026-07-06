<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs($this->superAdmin());

        foreach (Permission::FEATURES as $name => $label) {
            Permission::firstOrCreate(['name' => $name], ['label' => $label]);
        }
    }

    public function test_role_can_be_created_with_permissions(): void
    {
        $ids = Permission::whereIn('name', ['customers', 'vehicles'])->pluck('id')->all();

        $this->postJson('/api/roles', ['name' => 'Front Desk', 'permissions' => $ids])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'Front Desk')
            ->assertJsonCount(2, 'data.permissions');
    }

    public function test_role_requires_at_least_one_permission(): void
    {
        $this->postJson('/api/roles', ['name' => 'Empty', 'permissions' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['permissions']);
    }

    public function test_updating_role_syncs_permissions(): void
    {
        $role = Role::create(['name' => 'Desk']);
        $role->permissions()->sync(Permission::where('name', 'customers')->pluck('id'));

        $newIds = Permission::whereIn('name', ['invoices', 'service_jobs', 'vehicles'])->pluck('id')->all();

        $this->putJson("/api/roles/{$role->id}", ['name' => 'Desk Plus', 'permissions' => $newIds])
            ->assertOk()
            ->assertJsonPath('data.name', 'Desk Plus')
            ->assertJsonCount(3, 'data.permissions');
    }

    public function test_role_in_use_cannot_be_deleted(): void
    {
        $role = Role::create(['name' => 'Busy']);
        $role->permissions()->sync(Permission::where('name', 'customers')->pluck('id'));
        User::factory()->create(['role_id' => $role->id]);

        $this->deleteJson("/api/roles/{$role->id}")->assertStatus(422);

        $unused = Role::create(['name' => 'Unused']);
        $this->deleteJson("/api/roles/{$unused->id}")->assertOk();
    }

    public function test_permission_grants_menu_feature_access(): void
    {
        $user = $this->userWithPermissions(['invoices']);
        Sanctum::actingAs($user);

        $this->getJson('/api/invoices')->assertOk();
        $this->getJson('/api/customers')->assertStatus(403);

        $this->assertContains('invoices', $user->fresh()->permissionNames());
    }
}
