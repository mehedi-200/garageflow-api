<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_valid_credentials_returns_token_and_user(): void
    {
        User::factory()->create(['email' => 'admin@test.com', 'role' => 'admin']);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'admin')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'email']]]);
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

    public function test_any_authenticated_user_can_access_all_resources(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'mechanic']));

        $this->getJson('/api/mechanics')->assertOk();
        $this->getJson('/api/customers')->assertOk();
        $this->getJson('/api/invoices')->assertOk();
    }

    public function test_admin_can_create_mechanic_account(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'admin']));

        $this->postJson('/api/mechanics', [
            'name' => 'New Mechanic',
            'email' => 'mech@test.com',
            'password' => 'password123',
        ])->assertStatus(201)->assertJsonPath('data.role', 'mechanic');

        $this->assertDatabaseHas('users', ['email' => 'mech@test.com', 'role' => 'mechanic']);
    }
}
