<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials_returns_token_and_permissions(): void
    {
        User::factory()->create(['email' => 'admin@test.com', 'is_admin' => true]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.is_admin', true)
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email', 'permissions']]]);
    }

    public function test_login_with_wrong_password_is_rejected(): void
    {
        User::factory()->create(['email' => 'admin@test.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_unauthenticated_request_gets_json_401(): void
    {
        $this->getJson('/api/profile')
            ->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_user_without_permission_is_blocked(): void
    {
        Sanctum::actingAs($this->userWithPermissions(['vehicles']));

        $this->getJson('/api/customers')->assertStatus(403);
        $this->getJson('/api/permissions')->assertStatus(403);
        $this->postJson('/api/roles', [])->assertStatus(403);
        $this->getJson('/api/vehicles')->assertOk();
    }

    public function test_super_admin_bypasses_all_permissions(): void
    {
        Sanctum::actingAs($this->superAdmin());

        $this->getJson('/api/customers')->assertOk();
        $this->getJson('/api/invoices')->assertOk();
        $this->getJson('/api/permissions')->assertOk();
    }

    public function test_user_is_created_with_a_role_assigned(): void
    {
        Sanctum::actingAs($this->superAdmin());
        $role = Role::create(['name' => 'Front Desk']);

        $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@test.com',
            'password' => 'password123',
            'role_id' => $role->id,
        ])->assertStatus(201)->assertJsonPath('data.role.name', 'Front Desk');

        $this->assertDatabaseHas('users', ['email' => 'new@test.com', 'role_id' => $role->id, 'is_admin' => false]);
    }

    public function test_role_is_required_when_creating_a_user(): void
    {
        Sanctum::actingAs($this->superAdmin());

        $this->postJson('/api/users', [
            'name' => 'New User',
            'email' => 'new@test.com',
            'password' => 'password123',
        ])->assertStatus(422)->assertJsonValidationErrors(['role_id']);
    }

    public function test_super_admin_cannot_be_deleted(): void
    {
        Sanctum::actingAs($this->superAdmin());
        $otherAdmin = $this->superAdmin();

        $this->deleteJson("/api/users/{$otherAdmin->id}")->assertStatus(422);
    }
}
