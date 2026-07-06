<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\ServiceJob;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Sanctum::actingAs($this->superAdmin());
    }

    public function test_search_finds_vehicles_by_brand(): void
    {
        Vehicle::factory()->create(['brand' => 'Toyota', 'model' => 'Premio']);

        $this->getJson('/api/search?q=Toyota')
            ->assertOk()
            ->assertJsonPath('data.vehicles.0.brand', 'Toyota');
    }

    public function test_search_finds_jobs_by_service_type_and_mechanic_name(): void
    {
        $mechanic = User::factory()->create(['name' => 'Karim Mechanic']);
        ServiceJob::factory()->create(['service_type' => 'Bodywork', 'mechanic_id' => $mechanic->id]);

        $this->getJson('/api/search?q=Bodywork')->assertOk()
            ->assertJsonPath('data.jobs.0.service_type', 'Bodywork');
        $this->getJson('/api/search?q=Karim')->assertOk()
            ->assertJsonPath('data.jobs.0.mechanic.name', 'Karim Mechanic');
        $this->getJson('/api/search?q=Karim')->assertOk()
            ->assertJsonPath('data.users.0.name', 'Karim Mechanic');
    }

    public function test_search_finds_invoices_by_number(): void
    {
        $job = ServiceJob::factory()->create();
        $this->patchJson("/api/service-jobs/{$job->id}/status", ['status' => 'in_progress']);
        $this->postJson("/api/service-jobs/{$job->id}/items", ['name' => 'Part', 'cost' => 500]);
        $this->patchJson("/api/service-jobs/{$job->id}/status", ['status' => 'completed']);

        $this->getJson('/api/search?q=INV-')
            ->assertOk()
            ->assertJsonCount(1, 'data.invoices');
    }

    public function test_search_finds_customers_by_email(): void
    {
        Customer::factory()->create(['email' => 'special@mail.test']);

        $this->getJson('/api/search?q=special@mail')
            ->assertOk()
            ->assertJsonCount(1, 'data.customers');
    }
}
