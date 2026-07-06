<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        return $admin;
    }

    public function test_admin_can_create_customer(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/customers', [
            'name' => 'Rahim Uddin',
            'phone' => '01712345678',
        ])->assertStatus(201)->assertJsonPath('data.name', 'Rahim Uddin');

        $this->assertDatabaseHas('customers', ['phone' => '01712345678']);
    }

    public function test_customer_validation_rejects_missing_fields(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/api/customers', ['email' => 'not-required-alone@test.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_mechanic_can_also_create_customer(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'mechanic']));

        $this->postJson('/api/customers', [
            'name' => 'Created By Mechanic',
            'phone' => '01812345678',
        ])->assertStatus(201);
    }

    public function test_customers_can_be_searched_by_name(): void
    {
        $this->actingAsAdmin();
        Customer::factory()->create(['name' => 'Unique Rahim']);
        Customer::factory()->count(3)->create();

        $response = $this->getJson('/api/customers?q=Unique Rahim');

        $response->assertOk()->assertJsonPath('data.meta.total', 1);
    }

    public function test_deleting_a_customer_is_a_soft_delete(): void
    {
        $this->actingAsAdmin();
        $customer = Customer::factory()->create();

        $this->deleteJson("/api/customers/{$customer->id}")->assertOk();

        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }
}
